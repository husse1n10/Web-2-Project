<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Events\{AppointmentReminderBroadcast, NewRequestSubmitted, RequestDocumentUploaded};
use App\Models\{Appointment, Feedback, Message, Office, Service, ServiceRequest, SupportTicket, SupportTicketMessage, User};
use App\Notifications\AppointmentReminder;
use App\Notifications\NewSupportTicketNotification;
use App\Notifications\PhoneVerificationNotification;
use App\Notifications\SupportTicketReplyNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use App\Services\{ChatbotService, QrCodeService, PaymentService, PdfService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, Storage};
use Illuminate\Support\Str;
use App\Events\MessageSent;
use App\Events\MessagesRead;
use App\Events\SupportTicketMessageSent;

class CitizenController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────
    public function dashboard()
    {
        $user = Auth::user();
        $requests = $user->serviceRequests()->with(['service', 'office'])->latest()->paginate(10);
        $upcomingAppointments = $user->appointments()
            ->with('office')
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(5)
            ->get();

        return view('citizen.dashboard', compact('requests', 'upcomingAppointments'));
    }

    // ── Profile ───────────────────────────────────────────────────
    public function profile()
    {
        $user = Auth::user();
        $requests = $user->serviceRequests()->with(['service', 'office'])->latest()->take(5)->get();
        $paidRequests = $user->serviceRequests()
            ->with(['service', 'office'])
            ->where('payment_status', 'paid')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('citizen.profile', compact('user', 'requests', 'paidRequests'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $hasDocumentUpload = $request->hasFile('national_id_document');
        $wantsPasswordUpdate = $request->filled('password')
            || $request->filled('password_confirmation');

        if (!$hasDocumentUpload && !$wantsPasswordUpdate) {
            return back()->with('info', 'No profile changes were submitted.');
        }

        $rules = [];
        if ($hasDocumentUpload) {
            $rules['national_id_document'] = ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }

        if ($wantsPasswordUpdate) {
            $rules['current_password'] = ['required', 'current_password'];
            $rules['password'] = ['required', 'min:8', 'confirmed', 'different:current_password'];
        }

        $data = $request->validate($rules, [
            'current_password.current_password' => 'The current password you entered is incorrect.',
            'password.different' => 'New password must be different from the current one.',
        ]);

        if ($hasDocumentUpload) {
            if ($user->id_document) {
                Storage::disk('private')->delete($user->id_document);
            }

            $user->id_document = $request->file('national_id_document')->store('id_documents', 'private');
            $user->citizen_verification_status = 'pending';
            $user->citizen_verification_notes = null;
            $user->citizen_verified_at = null;
            $user->citizen_verified_by = null;
        }

        if ($wantsPasswordUpdate) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        $message = $hasDocumentUpload && $wantsPasswordUpdate
            ? 'Profile document and password updated successfully.'
            : ($hasDocumentUpload ? 'National ID document uploaded successfully. It is now pending admin validation.' : 'Password updated successfully.');

        return back()->with('success', $message);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = Auth::user();

        // Delete old avatar only if it's a local upload — OAuth avatars are
        // full URLs (e.g. https://lh3.googleusercontent.com/...) which the
        // public disk cannot resolve.
        if ($user->avatar && !Str::startsWith($user->avatar, ['http://', 'https://'])) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return back()->with('success', 'Profile photo updated!');
    }

    public function sendPhoneOtp(Request $request)
    {
        $data = $request->validate(['phone' => 'required|string|max:20']);
        $phone = $data['phone'];

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put('phone_otp_' . Auth::id(), [
            'otp'   => $otp,
            'phone' => $phone,
        ], now()->addMinutes(5));

        Notification::route('sms', $phone)
            ->notify(new PhoneVerificationNotification($otp));

        return back()->with('otp_sent', true);
    }

    public function verifyPhoneOtp(Request $request)
    {
        $data   = $request->validate(['otp' => 'required|string|size:6']);
        $cached = Cache::get('phone_otp_' . Auth::id());

        if (!$cached || $cached['otp'] !== $data['otp']) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.'])->with('otp_sent', true);
        }

        $user = Auth::user();
        $user->phone            = $cached['phone'];
        $user->phone_verified_at = now();
        $user->save();

        Cache::forget('phone_otp_' . Auth::id());

        return back()->with('success', 'Phone number verified successfully!');
    }

    // ── My Appointments ──────────────────────────────────────────
    public function myAppointments()
    {
        $user = Auth::user();

        $appointments = $user->appointments()
            ->with(['office', 'request.service'])
            ->orderByRaw("CASE WHEN appointment_date >= CURRENT_DATE THEN 0 ELSE 1 END")
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(15);

        $requestsNeedingAppointments = $user->serviceRequests()
            ->with(['service', 'office'])
            ->whereDoesntHave('appointment', fn ($query) => $query->whereIn('status', ['scheduled', 'confirmed']))
            ->whereNotIn('status', ['rejected', 'completed'])
            ->latest()
            ->get();

        return view('citizen.appointments.index', compact('appointments', 'requestsNeedingAppointments'));
    }

    // ── My Payments ──────────────────────────────────────────────
    public function myPayments()
    {
        $requests = Auth::user()->serviceRequests()
            ->with(['service', 'office'])
            ->latest()
            ->paginate(15);

        return view('citizen.payments.index', compact('requests'));
    }

    // ── Browse Services ───────────────────────────────────────────
    public function browseOffices(Request $request)
    {
        $municipalities = \App\Models\Municipality::where('is_active', true)
            ->orderBy('name')
            ->get();

        $query = Office::where('is_active', true)
            ->with(['municipality', 'services']);

        if ($search = $request->search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($mid = $request->municipality_id) {
            $query->where('municipality_id', $mid);
        }

        $offices = $query->withCount('requests')->paginate(12);

        $mapOffices = $offices->getCollection()
            ->filter(fn ($office) => !is_null($office->latitude) && !is_null($office->longitude))
            ->map(function ($office) {
                return [
                    'id' => $office->id,
                    'name' => $office->name,
                    'municipality' => $office->municipality?->name,
                    'address' => $office->address,
                    'latitude' => (float) $office->latitude,
                    'longitude' => (float) $office->longitude,
                    'phone' => $office->phone,
                    'services_count' => $office->services->count(),
                    'show_url' => route('citizen.offices.show', $office),
                ];
            })
            ->values();

        return view('citizen.offices.index', compact('offices', 'municipalities', 'mapOffices'));
    }

    public function showOffice(Office $office)
    {
        $office->load(['services.category', 'feedbacks' => fn ($q) => $q->latest()->limit(5)]);
        return view('citizen.offices.show', compact('office'));
    }

    // ── Service Request ───────────────────────────────────────────
    public function showService(Service $service)
    {
        $service->load('office');

        $convertedPrices = [];
        $baseCurrency = $service->currency ?? 'USD';
        $targets = array_diff(['USD', 'LBP', 'EUR'], [$baseCurrency]);
        $paymentService = app(PaymentService::class);

        foreach ($targets as $currency) {
            $convertedPrices[$currency] = $paymentService->convertCurrency(
                $service->price, $baseCurrency, $currency
            );
        }

        return view('citizen.services.show', compact('service', 'convertedPrices'));
    }

    public function submitRequest(Request $request, Service $service)
    {
        // Documents are required only if the service declares required documents.
        $hasRequiredDocs = is_array($service->required_documents) && count($service->required_documents) > 0;
        $documentsRule   = $hasRequiredDocs ? 'required|array|min:1' : 'nullable|array';

        $request->validate([
            'notes'         => 'nullable|string|max:1000',
            'documents'     => $documentsRule,
            'documents.*'   => 'file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $serviceRequest = DB::transaction(function () use ($request, $service) {
            $serviceRequest = ServiceRequest::create([
                'reference_number' => ServiceRequest::generateReference(),
                'citizen_id'       => Auth::id(),
                'service_id'       => $service->id,
                'office_id'        => $service->office_id,
                'notes'            => $request->notes,
                'amount_paid'      => $service->price,
            ]);

            foreach ($request->file('documents') ?? [] as $file) {
                $path = $file->store('request_documents/' . $serviceRequest->id, 'private');
                $document = $serviceRequest->documents()->create([
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_by'   => 'citizen',
                ]);

                event(new RequestDocumentUploaded($serviceRequest, $document));
            }

            $qrPath = app(QrCodeService::class)->generate($serviceRequest);
            $serviceRequest->update(['qr_code' => $qrPath]);

            return $serviceRequest;
        });

        event(new NewRequestSubmitted($serviceRequest));

        return redirect()->route('citizen.payment', $serviceRequest)
                        ->with('success', 'Request submitted. Please complete payment.');
    }

    // ── Payment ───────────────────────────────────────────────────
    public function showPayment(ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);
        return view('citizen.payment', compact('serviceRequest'));
    }

    public function processPayment(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);

        $data = $request->validate(['payment_method' => 'required|in:card,crypto']);

        $result = app(PaymentService::class)->process($serviceRequest, $data['payment_method'], $request->all());

        if (!$result['success']) {
            return back()->withErrors(['payment' => $result['message']]);
        }

        // Card: redirect to Stripe Checkout — payment confirmed on return
        if ($data['payment_method'] === 'card') {
            $serviceRequest->update([
                'payment_method' => 'card',
                'transaction_id' => $result['session_id'],
            ]);
            return redirect()->away($result['redirect_url']);
        }

        // Crypto: redirect to NOWPayments hosted invoice — webhook confirms payment
        $serviceRequest->update([
            'payment_method' => 'crypto',
            'transaction_id' => $result['invoice_id'],
        ]);
        return redirect()->away($result['invoice_url']);
    }

    public function paymentSuccess(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);

        // Stripe path — has a session_id in the return URL.
        if ($sessionId = $request->query('session_id')) {
            $verified = app(PaymentService::class)->verifyStripeSession($sessionId, $serviceRequest);

            if ($verified['success']) {
                $serviceRequest->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $verified['transaction_id'],
                ]);
                return redirect()->route('citizen.requests.show', $serviceRequest)
                    ->with('success', 'Payment successful! Your request is now being processed.');
            }

            return redirect()->route('citizen.payment', $serviceRequest)
                ->withErrors(['payment' => $verified['message']]);
        }

        // NOWPayments path — webhook is the source of truth. If it already fired,
        // the request is paid; otherwise we tell the citizen we're still waiting
        // for blockchain confirmation.
        if ($serviceRequest->payment_status === 'paid') {
            return redirect()->route('citizen.requests.show', $serviceRequest)
                ->with('success', 'Crypto payment received! Your request is now being processed.');
        }

        return redirect()->route('citizen.requests.show', $serviceRequest)
            ->with('warning', 'Payment is being confirmed on the blockchain. This page will update once it clears (usually a few minutes).');
    }

    public function paymentCancel(ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);

        return redirect()->route('citizen.payment', $serviceRequest)
            ->with('warning', 'Payment was cancelled. You can try again.');
    }

    // ── My Requests ───────────────────────────────────────────────
    public function myRequests(Request $request)
    {
            $query = Auth::user()
                ->serviceRequests()
                ->with(['service', 'office'])
                ->withCount([
                    'messages as unread_messages_count' => function($q){
                        $q->whereNull('read_at')
                            ->where('sender_id' , '!=', Auth::id());
                    }
                ])
                ->latest();

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($paymentStatus = $request->payment_status) {
            if ($paymentStatus === 'unpaid') {
                $query->where('payment_status', '!=', 'paid');
            } else {
                $query->where('payment_status', $paymentStatus);
            }
        }

        if ($search = trim((string) $request->search)) {
            $query->where(function ($builder) use ($search) {
                $builder->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('service', fn ($serviceQuery) => $serviceQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('office', fn ($officeQuery) => $officeQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $requests = $query->paginate(15);
        $requests->appends($request->query());
        return view('citizen.requests.index', compact('requests'));
    }

    public function showRequest(ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);

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

        $serviceRequest->load(['service', 'office', 'documents', 'statusLogs.changedBy', 'messages.sender', 'appointment']);

        return view('citizen.requests.show', compact('serviceRequest'));
    }

    public function trackByQr(string $reference)
    {
        $req = ServiceRequest::where('reference_number', $reference)
                            ->with(['service', 'office', 'statusLogs'])->firstOrFail();
        return view('citizen.requests.track', compact('req'));
    }

    // ── Receipt Download ──────────────────────────────────────────
    public function downloadReceipt(ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);
        abort_unless($serviceRequest->payment_status === 'paid', 403);

        $path = app(PdfService::class)->generateReceipt($serviceRequest);
        return app(PdfService::class)->stream($path, "receipt-{$serviceRequest->reference_number}.pdf");
    }

    // ── Appointments ──────────────────────────────────────────────
    public function bookAppointment(Request $request)
    {
        $data = $request->validate([
            'office_id'          => 'required|exists:offices,id',
            'service_request_id' => 'nullable|exists:service_requests,id',
            'appointment_date'   => 'required|date|after:today',
            'appointment_time'   => 'required|date_format:H:i',
            'notes'              => 'nullable|string',
        ]);

        // Ensure a linked request belongs to this citizen and has no active appointment.
        if (!empty($data['service_request_id'])) {
            $serviceRequest = ServiceRequest::where('citizen_id', Auth::id())
                ->whereKey($data['service_request_id'])
                ->firstOrFail();

            if ((int) $data['office_id'] !== (int) $serviceRequest->office_id) {
                return back()->withErrors([
                    'appointment' => 'Selected office does not match this service request.',
                ])->withInput();
            }

            if ($serviceRequest->appointment()->whereIn('status', ['scheduled', 'confirmed'])->exists()) {
                return back()->withErrors([
                    'appointment' => 'This request already has an appointment.',
                ])->withInput();
            }
        }

        // Block double-booking: same office + date + time, ignoring cancelled/completed.
        $conflict = Appointment::where('office_id', $data['office_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->exists();

        if ($conflict) {
            return back()
                ->withErrors(['appointment_time' => 'This time slot is already booked. Please pick another time.'])
                ->withInput();
        }

        $appointment = Appointment::create(array_merge($data, ['citizen_id' => Auth::id()]));
        event(new AppointmentReminderBroadcast($appointment, 'appointment_booked'));
        Auth::user()?->notify(new AppointmentReminder($appointment->fresh(['request']), 'appointment_booked'));

        return back()->with('success', 'Appointment booked successfully.');
    }

    public function cancelAppointment(Appointment $appointment)
    {
        abort_unless($appointment->citizen_id === Auth::id(), 403);

        if (in_array($appointment->status, ['cancelled', 'completed'], true)) {
            return back()->withErrors([
                'appointment' => 'This appointment can no longer be cancelled.',
            ]);
        }

        $appointment->update(['status' => 'cancelled']);

        event(new AppointmentReminderBroadcast($appointment->fresh(), 'appointment_cancelled'));

        return back()->with('success', 'Appointment cancelled.');
    }

    // ── Feedback ──────────────────────────────────────────────────
    public function submitFeedback(Request $request)
    {
        $data = $request->validate([
            'office_id'          => 'required|exists:offices,id',
            'service_request_id' => 'nullable|exists:service_requests,id',
            'rating'             => 'required|integer|min:1|max:5',
            'comment'            => 'nullable|string|max:1000',
        ]);

        if (!empty($data['service_request_id'])) {
            $alreadyExists = Feedback::where('citizen_id', Auth::id())
                ->where('service_request_id', $data['service_request_id'])
                ->exists();

            if ($alreadyExists) {
                return back()->withErrors([
                    'feedback' => 'You have already submitted feedback for this request.'
                ])->withInput();
            }
        }

        Feedback::create(array_merge($data, ['citizen_id' => Auth::id()]));

        return back()->with('success', 'Thank you for your feedback!');
    }

    // ── Messages ──────────────────────────────────────────────────
    public function sendMessage(Request $request, ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);
        $data = $request->validate(['body' => 'required|string|max:2000']);

        $msg = $serviceRequest->messages()->create([
            'sender_id' => Auth::id(),
            'body'      => $data['body'],
        ]);

        event(new MessageSent($msg));

        return response()->json(['message' => $msg->load('sender'), 'success' => true]);
    }

    // ── Documents ─────────────────────────────────────────────────
    public function downloadDocument(ServiceRequest $serviceRequest, string $docId)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);
        $doc = $serviceRequest->documents()->findOrFail($docId);
        return response()->download(
            storage_path('app/private/' . $doc->file_path),
            $doc->original_name
        );
    }

    public function getMessages(ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);

        return response()->json([
            'messages' => $serviceRequest->messages()->with('sender')->get()
        ]);
    }

    public function markMessagesRead(ServiceRequest $serviceRequest)
    {
        abort_unless($serviceRequest->citizen_id === Auth::id(), 403);

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

    // ── Support Tickets ───────────────────────────────────────────
    public function supportIndex()
    {
        $tickets = Auth::user()->supportTickets()
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('sender_id', '!=', Auth::id())->whereNull('read_at');
            }])
            ->latest('updated_at')
            ->paginate(15);

        return view('citizen.support.index', compact('tickets'));
    }

    public function supportCreate()
    {
        return view('citizen.support.create');
    }

    public function supportStore(Request $request)
    {
        $data = $request->validate([
            'subject'    => 'required|string|max:200',
            'body'       => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
        ]);

        $attachmentData = $this->storeSupportAttachment($request);

        $ticket = DB::transaction(function () use ($data, $attachmentData) {
            $ticket = SupportTicket::create([
                'user_id' => Auth::id(),
                'subject' => $data['subject'],
                'status'  => 'open',
                'last_reply_at' => now(),
            ]);

            SupportTicketMessage::create(array_merge([
                'support_ticket_id' => $ticket->id,
                'sender_id' => Auth::id(),
                'body'      => $data['body'],
            ], $attachmentData));

            return $ticket;
        });

        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewSupportTicketNotification(
                $ticket,
                Str::limit($data['body'], 140),
                Auth::user()->name,
            ));
        }

        return redirect()->route('citizen.support.show', $ticket)
            ->with('success', 'Your support ticket has been submitted. An admin will reply soon.');
    }

    public function supportShow(SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);

        $ticket->load(['messages.sender']);

        $ticket->messages()
            ->where('sender_id', '!=', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('citizen.support.show', compact('ticket'));
    }

    public function supportReply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === Auth::id(), 403);
        abort_if($ticket->status === 'closed', 403, 'This ticket is closed.');

        $data = $request->validate([
            'body'       => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
        ]);

        $attachmentData = $this->storeSupportAttachment($request);

        $newMessage = DB::transaction(function () use ($data, $ticket, $attachmentData) {
            $msg = SupportTicketMessage::create(array_merge([
                'support_ticket_id' => $ticket->id,
                'sender_id' => Auth::id(),
                'body'      => $data['body'],
            ], $attachmentData));

            $ticket->update([
                'status' => 'open',
                'last_reply_at' => now(),
            ]);

            return $msg;
        });

        broadcast(new SupportTicketMessageSent($newMessage))->toOthers();

        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new SupportTicketReplyNotification(
                $ticket,
                Str::limit($data['body'], 140),
                Auth::user()->name,
                'citizen',
            ));
        }

        return back()->with('success', 'Reply sent.');
    }

    // ── AI Chatbot ────────────────────────────────────────────────
    public function chatbotAsk(Request $request, ChatbotService $bot)
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'required_with:history|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:2000',
        ]);

        $result = $bot->ask($data['history'] ?? [], $data['message']);

        return response()->json($result, $result['ok'] ? 200 : 503);
    }

    private function storeSupportAttachment(Request $request): array
    {
        if (!$request->hasFile('attachment')) {
            return [];
        }

        $file = $request->file('attachment');
        $path = $file->store('support-attachments', 'public');

        return [
            'attachment'      => $path,
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_size' => $file->getSize(),
        ];
    }
}
