<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Events\{AppointmentReminderBroadcast, ServiceRequestStatusUpdated};
use App\Models\{Appointment, Feedback, Office, Service, ServiceCategory, ServiceRequest};
use App\Notifications\AppointmentReminder;
use App\Notifications\RequestStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Events\MessageSent;
use App\Events\MessagesRead;

class OfficeController extends Controller
{
    private function currentOffice(): Office
    {
        return Auth::user()->offices()->firstOrFail();
    }

    // ── Dashboard ─────────────────────────────────────────────────
    public function dashboard()
    {
        $office = $this->currentOffice();
        $revenueTotals = $office->requests()
            ->where('service_requests.payment_status', 'paid')
            ->selectRaw("UPPER(COALESCE(service_requests.service_currency, 'USD')) as currency")
            ->selectRaw('SUM(COALESCE(service_requests.amount_paid, service_requests.service_price, 0)) as total')
            ->groupBy('currency')
            ->orderBy('currency')
            ->pluck('total', 'currency')
            ->map(fn ($total) => (float) $total);

        $stats = [
            'pending'              => $office->requests()->where('status', 'pending')->count(),
            'in_review'            => $office->requests()->where('status', 'in_review')->count(),
            'completed_this_month' => $office->requests()->where('status', 'completed')
                                            ->whereYear('completed_at', now()->year)
                                            ->whereMonth('completed_at', now()->month)
                                            ->count(),
            'revenue_breakdown'    => Service::formatCurrencyBreakdown($revenueTotals),
            'avg_rating'           => $office->feedbacks()->avg('rating') ?? 0,
            'pending_today'        => $office->requests()->whereDate('created_at', today())->count(),
        ];

        $recentRequests = $office->requests()
                                ->with(['citizen', 'service'])
                                ->whereIn('status', ['pending', 'in_review'])
                                ->latest()->limit(8)->get();

        $todayAppointments = $office->appointments()
                                    ->with('citizen')
                                    ->whereDate('appointment_date', today())
                                    ->orderBy('appointment_time')
                                    ->limit(5)->get();

        $recentFeedback = $office->feedbacks()
                                ->with('citizen')
                                ->latest()->limit(4)->get();

        return view('office.dashboard', compact('office', 'stats', 'recentRequests', 'todayAppointments', 'recentFeedback'));
    }

    // ── Office Profile ────────────────────────────────────────────
    public function editProfile()
    {
        $office = $this->currentOffice();
        return view('office.profile.edit', compact('office'));
    }

    public function updateProfile(Request $request)
    {
        $office = $this->currentOffice();
        $data   = $request->validate([
            'name'          => 'required|string|max:255',
            'address'       => 'required|string',
            'latitude'      => 'nullable|numeric',
            'longitude'     => 'nullable|numeric',
            'phone'         => 'nullable|string|max:20',
            'email'         => 'nullable|email',
            'website'       => 'nullable|url',
            'working_hours' => 'nullable|array',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('office_logos', 'public');
        }

        $office->update($data);
        return back()->with('success', 'Profile updated.');
    }

    // ── Services ──────────────────────────────────────────────────
    public function services()
    {
        $office    = $this->currentOffice();
        $services  = $office->services()->with('category')->paginate(15);
        $categories= $office->categories;
        return view('office.services.index', compact('office', 'services', 'categories'));
    }

    public function storeService(Request $request)
    {
        $office = $this->currentOffice();
        $data   = $request->validate([
            'name'                    => 'required|string|max:255',
            'description'             => 'nullable|string',
            'price'                   => 'required|numeric|min:0|decimal:0,2',
            'currency'                => 'required|string|max:5',
            'estimated_duration_days' => 'required|integer|min:1',
            'required_documents'      => 'nullable|array',
            'category_id'             => 'nullable|exists:service_categories,id',
        ]);

        $office->services()->create($data);
        return back()->with('success', 'Service created.');
    }

    public function updateService(Request $request, Service $service)
    {
        $this->authorizeOfficeOwnership($service->office_id);
        $data = $request->validate([
            'name'                    => 'required|string|max:255',
            'price'                   => 'required|numeric|decimal:0,2',
            'currency'                => 'required|string|max:5',
            'estimated_duration_days' => 'required|integer|min:1',
            'is_active'               => 'boolean',
        ]);
        $service->update($data);
        return back()->with('success', 'Service updated.');
    }

    public function destroyService(Service $service)
    {
        $this->authorizeOfficeOwnership($service->office_id);
        $service->delete();
        return back()->with('success', 'Service deleted.');
    }

    // ── Request Handling ──────────────────────────────────────────
    public function requests(Request $request)
    {
        $office = $this->currentOffice();

        $query = $office->requests()
            ->with(['citizen', 'service', 'assignee:id,name'])
            ->withCount([
                'messages as unread_messages_count' => function ($q) {
                    $q->whereNull('read_at')
                        ->where('sender_id', '!=', Auth::id());
                }
            ]);

        if ($request->boolean('overdue')) {
            $query->whereNotIn('status', ['completed', 'rejected'])
                  ->whereNotNull('due_at')
                  ->where('due_at', '<', now());
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } elseif ($request->assigned_to === 'me') {
                $query->where('assigned_to', Auth::id());
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        if ($request->status) $query->where('status', $request->status);
        if ($request->search) {
            $search = trim((string) $request->search);
            $query->where(function ($builder) use ($search) {
                $builder->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('citizen', fn ($citizenQuery) => $citizenQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->latest()->paginate(20);
        $requests->appends($request->query());

        $officeStaff = $office->users()
            ->where('users.role', 'office_user')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name']);
        $overdueCount = $office->requests()
            ->whereNotIn('status', ['completed', 'rejected'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->count();

        return view('office.requests.index', compact('requests', 'officeStaff', 'overdueCount'));
    }

    public function showRequest(ServiceRequest $serviceRequest)
    {
        $this->authorizeOfficeOwnership($serviceRequest->office_id);

        $readMessageIds = $serviceRequest->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', Auth::id())
            ->pluck('id')
            ->toArray();

        if (!empty($readMessageIds)) {
            $serviceRequest->messages()
                ->whereIn('id', $readMessageIds)
                ->update(['read_at' => now()]);

            event(new MessagesRead($serviceRequest->id, $readMessageIds, Auth::id()));
        }

        $serviceRequest->load(['citizen', 'service', 'documents', 'statusLogs.changedBy', 'messages.sender', 'appointment', 'assignee:id,name']);

        $officeStaff = $this->currentOffice()->users()
            ->where('users.role', 'office_user')
            ->orderBy('users.name')
            ->get(['users.id', 'users.name']);

        return view('office.requests.show', compact('serviceRequest', 'officeStaff'));
    }

    public function viewDocument(ServiceRequest $serviceRequest, string $docId)
    {
        $this->authorizeOfficeOwnership($serviceRequest->office_id);

        $doc = $serviceRequest->documents()->findOrFail($docId);
        abort_unless(Storage::disk('private')->exists($doc->file_path), 404, 'Document file not found.');

        return Storage::disk('private')->response(
            $doc->file_path,
            $doc->original_name,
            ['Cache-Control' => 'private, no-store'],
            'inline'
        );
    }

    public function assignRequest(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeOfficeOwnership($serviceRequest->office_id);

        $data = $request->validate([
            'assigned_to' => 'nullable|integer|exists:users,id',
            'due_at'      => 'nullable|date|after_or_equal:today',
        ]);

        // Assignee must be a user attached to this office (any role on the pivot).
        if (!empty($data['assigned_to'])) {
            $belongs = $this->currentOffice()->users()->where('users.id', $data['assigned_to'])->exists();
            if (!$belongs) {
                return back()->withErrors(['assigned_to' => 'That user is not a member of this office.']);
            }
        }

        $serviceRequest->update([
            'assigned_to' => $data['assigned_to'] ?? null,
            'due_at'      => $data['due_at'] ?? $serviceRequest->due_at,
        ]);

        return back()->with('success', 'Request assignment updated.');
    }

    public function updateRequestStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeOfficeOwnership($serviceRequest->office_id);

        $data = $request->validate([
            'status'  => 'required|in:in_review,missing_documents,approved,rejected,completed',
            'comment' => 'nullable|string',
        ]);

        $oldStatus = $serviceRequest->status;
        $serviceRequest->update([
            'status'       => $data['status'],
            'office_notes' => $request->office_notes,
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);

        $serviceRequest->statusLogs()->create([
            'changed_by'  => Auth::id(),
            'from_status' => $oldStatus,
            'to_status'   => $data['status'],
            'comment'     => $data['comment'],
        ]);

        // Notify citizen
        $serviceRequest->citizen->notify(new RequestStatusUpdated($serviceRequest));
        event(new ServiceRequestStatusUpdated($serviceRequest, $oldStatus, $data['comment'] ?? null));

        return back()->with('success', 'Request status updated.');
    }

    // ── Feedback ──────────────────────────────────────────────────
    public function feedback()
    {
        $office   = $this->currentOffice();
        $feedback = $office->feedbacks()->with('citizen')->latest()->paginate(15);
        $averageRating = (float) ($office->feedbacks()->avg('rating') ?? 0);

        return view('office.feedback.index', compact('feedback', 'averageRating'));
    }

    public function replyFeedback(Request $request, Feedback $feedback)
    {
        $this->authorizeOfficeOwnership($feedback->office_id);
        $data = $request->validate([
            'office_reply'    => 'required|string',
            'reply_is_public' => 'boolean',
        ]);
        $feedback->update($data);
        return back()->with('success', 'Reply posted.');
    }

    // ── Appointments ──────────────────────────────────────────────
    public function appointments()
    {
        $office       = $this->currentOffice();
        $appointments = $office->appointments()->with('citizen')->orderBy('appointment_date')->paginate(20);
        return view('office.appointments.index', compact('appointments'));
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        $this->authorizeOfficeOwnership($appointment->office_id);
        $data = $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed',
        ]);

        $appointment->update($data);

        if ($data['status'] === 'confirmed') {
            event(new AppointmentReminderBroadcast($appointment->fresh(), 'appointment_confirmed'));
            $appointment->loadMissing(['citizen', 'request']);
            $appointment->citizen?->notify(new AppointmentReminder($appointment, 'appointment_confirmed'));
        }

        return back()->with('success', 'Appointment updated.');
    }

    // ── Messages ──────────────────────────────────────────────────
    public function sendMessage(Request $request, ServiceRequest $serviceRequest)
    {
        $this->authorizeOfficeOwnership($serviceRequest->office_id);
        $data = $request->validate(['body' => 'required|string|max:2000']);

        $msg = $serviceRequest->messages()->create([
            'sender_id' => Auth::id(),
            'body'      => $data['body'],
        ]);

        event(new MessageSent($msg));

        return response()->json(['message' => $msg->load('sender'), 'success' => true]);
    }

    // ── PDF Downloads ─────────────────────────────────────────────
    public function downloadPdf(ServiceRequest $serviceRequest, string $type)
    {
        $this->authorizeOfficeOwnership($serviceRequest->office_id);
        $svc = app(\App\Services\PdfService::class);

        abort_unless(match ($type) {
            'receipt' => $serviceRequest->canDownloadReceipt(),
            'approval' => $serviceRequest->canDownloadApprovalLetter(),
            'certificate' => $serviceRequest->canDownloadCertificate(),
            default => false,
        }, 403);

        $path = match ($type) {
            'receipt'     => $svc->generateReceipt($serviceRequest),
            'approval'    => $svc->generateApprovalLetter($serviceRequest),
            'certificate' => $svc->generateCertificate($serviceRequest),
            default       => abort(404),
        };

        return $svc->stream($path, "{$type}-{$serviceRequest->reference_number}.pdf");
    }

    // ── Helpers ───────────────────────────────────────────────────
    private function authorizeOfficeOwnership(int $officeId): void
    {
        $office = $this->currentOffice();
        abort_unless($office->id === $officeId, 403);
    }

    public function getMessages(ServiceRequest $serviceRequest)
    {
        $this->authorizeOfficeOwnership($serviceRequest->office_id);

        return response()->json([
            'messages' => $serviceRequest->messages()->with('sender')->get()
        ]);
    }

    public function markMessagesRead(ServiceRequest $serviceRequest)
    {
        $this->authorizeOfficeOwnership($serviceRequest->office_id);

        $readMessageIds = $serviceRequest->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', Auth::id())
            ->pluck('id')
            ->toArray();

        if (!empty($readMessageIds)) {
            $serviceRequest->messages()
                ->whereIn('id', $readMessageIds)
                ->update(['read_at' => now()]);

            event(new MessagesRead($serviceRequest->id, $readMessageIds, Auth::id()));
        }

        return response()->json([
            'success' => true,
            'message_ids' => $readMessageIds,
        ]);
    }

}
