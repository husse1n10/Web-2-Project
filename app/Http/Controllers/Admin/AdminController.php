<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\SupportTicketMessageSent;
use App\Models\{Municipality, Office, ServiceRequest, SupportTicket, SupportTicketMessage, User};
use App\Notifications\SupportTicketReplyNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // Dashboard
    public function dashboard(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|in:all,this_month,last_30,last_90,this_year',
            'municipality_id' => 'nullable|integer|exists:municipalities,id',
            'office_id' => 'nullable|integer|exists:offices,id',
        ]);

        $period = $validated['period'] ?? 'this_month';
        $municipalityId = isset($validated['municipality_id']) ? (int) $validated['municipality_id'] : null;
        $officeId = isset($validated['office_id']) ? (int) $validated['office_id'] : null;

        if ($municipalityId && $officeId) {
            $officeBelongsToMunicipality = Office::whereKey($officeId)
                ->where('municipality_id', $municipalityId)
                ->exists();

            if (!$officeBelongsToMunicipality) {
                $officeId = null;
            }
        }

        [$currentStart, $currentEnd, $previousStart, $previousEnd, $periodLabel] = $this->resolveDashboardPeriod($period);

        $municipalities = Municipality::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $officeOptionsQuery = Office::query()
            ->with('municipality:id,name')
            ->orderBy('name');

        if ($municipalityId) {
            $officeOptionsQuery->where('municipality_id', $municipalityId);
        }

        $officeOptions = $officeOptionsQuery->get(['id', 'name', 'municipality_id']);

        // Requests and revenue (filter-aware)
        $currentRequestsQuery = ServiceRequest::query();
        $this->applyRequestDashboardScope($currentRequestsQuery, $currentStart, $currentEnd, $municipalityId, $officeId);

        $previousRequestsQuery = ServiceRequest::query();
        $this->applyRequestDashboardScope($previousRequestsQuery, $previousStart, $previousEnd, $municipalityId, $officeId);

        $totalRequests = (clone $currentRequestsQuery)->count();
        $previousTotalRequests = (clone $previousRequestsQuery)->count();

        $pendingRequests = (clone $currentRequestsQuery)
            ->where('status', 'pending')
            ->count();

        $totalRevenue = (clone $currentRequestsQuery)
            ->where('payment_status', 'paid')
            ->sum('amount_paid');

        $previousTotalRevenue = (clone $previousRequestsQuery)
            ->where('payment_status', 'paid')
            ->sum('amount_paid');

        // Platform totals with scope hints
        $currentUsersQuery = User::query()->where('role', 'citizen');
        if ($currentEnd) {
            $currentUsersQuery->where('created_at', '<=', $currentEnd);
        }
        $totalUsers = $currentUsersQuery->count();

        $previousUsersQuery = User::query()->where('role', 'citizen');
        if ($previousEnd) {
            $previousUsersQuery->where('created_at', '<=', $previousEnd);
        }
        $previousTotalUsers = $previousUsersQuery->count();

        $currentOfficesQuery = Office::query();
        if ($municipalityId) {
            $currentOfficesQuery->where('municipality_id', $municipalityId);
        }
        if ($officeId) {
            $currentOfficesQuery->whereKey($officeId);
        }
        if ($currentEnd) {
            $currentOfficesQuery->where('created_at', '<=', $currentEnd);
        }
        $totalOffices = $currentOfficesQuery->count();

        $previousOfficesQuery = Office::query();
        if ($municipalityId) {
            $previousOfficesQuery->where('municipality_id', $municipalityId);
        }
        if ($officeId) {
            $previousOfficesQuery->whereKey($officeId);
        }
        if ($previousEnd) {
            $previousOfficesQuery->where('created_at', '<=', $previousEnd);
        }
        $previousTotalOffices = $previousOfficesQuery->count();

        $stats = [
            'total_users' => $totalUsers,
            'total_offices' => $totalOffices,
            'total_requests' => $totalRequests,
            'pending_requests' => $pendingRequests,
            'total_revenue' => $totalRevenue,
        ];

        $trends = [
            'total_users' => $this->buildTrend($totalUsers, $previousTotalUsers),
            'total_offices' => $this->buildTrend($totalOffices, $previousTotalOffices),
            'total_requests' => $this->buildTrend($totalRequests, $previousTotalRequests),
            'total_revenue' => $this->buildTrend($totalRevenue, $previousTotalRevenue),
        ];

        // Recent requests (filter-aware)
        $recentRequestsQuery = ServiceRequest::with(['citizen', 'service', 'office'])
            ->latest()
            ->limit(10);

        $this->applyRequestDashboardScope($recentRequestsQuery, $currentStart, $currentEnd, $municipalityId, $officeId);
        $recentRequests = $recentRequestsQuery->get();

        // Top offices (filter-aware)
        $officeStatsQuery = Office::query()
            ->with('municipality:id,name');

        if ($municipalityId) {
            $officeStatsQuery->where('municipality_id', $municipalityId);
        }
        if ($officeId) {
            $officeStatsQuery->whereKey($officeId);
        }

        $officeStats = $officeStatsQuery
            ->withCount([
                'requests as requests_count' => function (Builder $query) use ($currentStart, $currentEnd): void {
                    if ($currentStart && $currentEnd) {
                        $query->whereBetween('created_at', [$currentStart, $currentEnd]);
                    }
                },
            ])
            ->having('requests_count', '>', 0)
            ->orderByDesc('requests_count')
            ->limit(5)
            ->get();

        // Monthly chart data (year grid + current filter scope)
        $monthlyRawQuery = ServiceRequest::query()
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month_num, COUNT(*) as total')
            ->whereYear('created_at', now()->year);

        $this->applyRequestDashboardScope($monthlyRawQuery, $currentStart, $currentEnd, $municipalityId, $officeId);

        $monthlyRaw = $monthlyRawQuery
            ->groupBy('month_num')
            ->orderBy('month_num')
            ->pluck('total', 'month_num')
            ->mapWithKeys(fn ($total, $month) => [(int) $month => (int) $total]);

        $chartLabels = [];
        $chartValues = [];

        for ($month = 1; $month <= 12; $month++) {
            $chartLabels[] = Carbon::create()->month($month)->format('M');
            $chartValues[] = (int) ($monthlyRaw[$month] ?? 0);
        }

        $dashboardFilters = [
            'period' => $period,
            'period_label' => $periodLabel,
            'municipality_id' => $municipalityId,
            'office_id' => $officeId,
            'is_scoped' => (bool) ($municipalityId || $officeId || $period !== 'this_month'),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'trends',
            'recentRequests',
            'officeStats',
            'municipalities',
            'officeOptions',
            'dashboardFilters',
            'chartLabels',
            'chartValues'
        ));
    }

    private function resolveDashboardPeriod(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'all' => [null, null, null, null, 'All time'],
            'last_30' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDays(59)->startOfDay(),
                $now->copy()->subDays(30)->endOfDay(),
                'Last 30 days',
            ],
            'last_90' => [
                $now->copy()->subDays(89)->startOfDay(),
                $now->copy()->endOfDay(),
                $now->copy()->subDays(179)->startOfDay(),
                $now->copy()->subDays(90)->endOfDay(),
                'Last 90 days',
            ],
            'this_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfDay(),
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
                'This year',
            ],
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfDay(),
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
                'This month',
            ],
        };
    }

    private function applyRequestDashboardScope(
        Builder $query,
        ?Carbon $start,
        ?Carbon $end,
        ?int $municipalityId,
        ?int $officeId
    ): void {
        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        if ($officeId) {
            $query->where('office_id', $officeId);
            return;
        }

        if ($municipalityId) {
            $query->whereHas('office', function (Builder $officeQuery) use ($municipalityId): void {
                $officeQuery->where('municipality_id', $municipalityId);
            });
        }
    }

    private function buildTrend(float|int $current, float|int $previous): array
    {
        $currentValue = (float) $current;
        $previousValue = (float) $previous;

        if ($previousValue === 0.0) {
            if ($currentValue === 0.0) {
                return ['text' => '0%', 'direction' => 'flat'];
            }

            return ['text' => 'New', 'direction' => 'up'];
        }

        $delta = (($currentValue - $previousValue) / abs($previousValue)) * 100;
        $roundedDelta = round($delta, 1);

        if ($roundedDelta === 0.0) {
            return ['text' => '0%', 'direction' => 'flat'];
        }

        $formatted = rtrim(rtrim(number_format(abs($roundedDelta), 1, '.', ''), '0'), '.');

        return [
            'text' => ($roundedDelta > 0 ? '+' : '-') . $formatted . '%',
            'direction' => $roundedDelta > 0 ? 'up' : 'down',
        ];
    }

    // Municipality Management
    public function municipalities()
    {
        $municipalities = Municipality::withCount('offices')->paginate(15);
        return view('admin.municipalities.index', compact('municipalities'));
    }

    public function storeMunicipality(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'region'  => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
        ]);
        Municipality::create($data);
        return back()->with('success', 'Municipality created successfully.');
    }

    public function updateMunicipality(Request $request, Municipality $municipality)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'region'    => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        $municipality->update($data);
        return back()->with('success', 'Municipality updated.');
    }

    public function destroyMunicipality(Municipality $municipality)
    {
        $municipality->delete();
        return back()->with('success', 'Municipality deleted.');
    }

    // Office Management
    public function offices()
    {
        $offices = Office::with('municipality')->paginate(15);
        return view('admin.offices.index', compact('offices'));
    }

    public function storeOffice(Request $request)
    {
        $data = $request->validate([
            'municipality_id' => 'required|exists:municipalities,id',
            'name'            => 'required|string|max:255',
            'address'         => 'required|string',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'phone'           => 'nullable|string|max:20',
            'email'           => 'nullable|email',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('office_logos', 'public');
        }

        Office::create($data);
        return back()->with('success', 'Office created.');
    }

    public function updateOffice(Request $request, Office $office)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'address'         => 'required|string',
            'municipality_id' => 'required|exists:municipalities,id',
            'is_active'       => 'boolean',
        ]);
        $office->update($data);
        return back()->with('success', 'Office updated.');
    }

    public function destroyOffice(Office $office)
    {
        $office->delete();
        return back()->with('success', 'Office deleted.');
    }

    // User Management
    public function users(Request $request)
    {
        $query = User::query()->with('citizenVerifier:id,name');

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if (in_array($request->verification_status, ['pending', 'approved', 'rejected', 'missing_document'], true)) {
            $query->where('role', 'citizen');

            if ($request->verification_status === 'missing_document') {
                $query->whereNull('id_document');
            } else {
                $query->where('citizen_verification_status', $request->verification_status);
            }
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('national_id', 'like', "%{$request->search}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function createOfficeUser(Request $request)
    {
        $data = $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8',
            'office_id' => 'required|exists:offices,id',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'office_user',
        ]);

        $user->offices()->attach($data['office_id'], ['role' => 'staff']);

        return back()->with('success', 'Office user created.');
    }

    public function updateUser(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ];

        if ($user->isCitizen()) {
            $rules['national_id'] = ['nullable', 'string', 'max:50'];
            $rules['citizen_verification_notes'] = ['nullable', 'string', 'max:500'];
        }

        $data = $request->validate($rules);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ];

        if ($user->isCitizen()) {
            $payload['national_id'] = $data['national_id'] ?? null;
            $payload['citizen_verification_notes'] = $data['citizen_verification_notes'] ?? null;
        }

        $user->update($payload);

        return back()->with('success', 'User information updated.');
    }

    public function toggleUserStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User account {$status}.");
    }

    public function downloadCitizenIdentityDocument(User $user)
    {
        abort_unless($user->isCitizen(), 404);
        abort_if(blank($user->id_document), 404);

        $disk = Storage::disk('private');
        abort_unless($disk->exists($user->id_document), 404);

        $extension = pathinfo($user->id_document, PATHINFO_EXTENSION);
        $filename = 'citizen-' . $user->id . '-national-id' . ($extension ? ".{$extension}" : '');

        $path = $disk->path($user->id_document);
        $mime = $disk->mimeType($user->id_document) ?: 'application/octet-stream';

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function approveCitizenIdentity(User $user)
    {
        abort_unless($user->isCitizen(), 404);

        if (blank($user->id_document)) {
            return back()->with('error', 'This citizen has not uploaded a National ID document yet.');
        }

        $user->forceFill([
            'citizen_verification_status' => 'approved',
            'citizen_verification_notes' => null,
            'citizen_verified_at' => now(),
            'citizen_verified_by' => auth()->id(),
        ])->save();

        return back()->with('success', 'Citizen identity approved.');
    }

    public function rejectCitizenIdentity(Request $request, User $user)
    {
        abort_unless($user->isCitizen(), 404);

        if (blank($user->id_document)) {
            return back()->with('error', 'This citizen has not uploaded a National ID document yet.');
        }

        $data = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $user->forceFill([
            'citizen_verification_status' => 'rejected',
            'citizen_verification_notes' => array_key_exists('notes', $data)
                ? $data['notes']
                : $user->citizen_verification_notes,
            'citizen_verified_at' => null,
            'citizen_verified_by' => auth()->id(),
        ])->save();

        return back()->with('success', 'Citizen identity rejected.');
    }

    // Reporting
    public function reports()
    {
        $requestsByOffice = Office::withCount('requests')
            ->orderBy('requests_count', 'desc')->get();

        $revenueByOffice = Office::withSum(
            ['requests as revenue' => fn ($q) => $q->where('payment_status', 'paid')],
            'amount_paid'
        )->get();

        $requestsByStatus = ServiceRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $monthlyRequests = ServiceRequest::selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')->pluck('total', 'month')
            ->mapWithKeys(fn ($total, $month) => [(int) $month => (int) $total]);

        return view('admin.reports', compact(
            'requestsByOffice', 'revenueByOffice', 'requestsByStatus', 'monthlyRequests'
        ));
    }

    // ── CSV Exports ───────────────────────────────────────────────
    public function exportReport(string $type)
    {
        return match ($type) {
            'requests' => $this->streamCsv(
                'requests-' . now()->format('Y-m-d') . '.csv',
                ['Reference', 'Citizen', 'Service', 'Office', 'Municipality', 'Status', 'Payment Status', 'Amount Paid', 'Submitted At', 'Completed At'],
                ServiceRequest::with(['citizen:id,name', 'service:id,name', 'office:id,name,municipality_id', 'office.municipality:id,name'])
                    ->orderByDesc('created_at')
                    ->lazy()
                    ->map(fn ($r) => [
                        $r->reference_number,
                        optional($r->citizen)->name ?? '-',
                        optional($r->service)->name ?? '-',
                        optional($r->office)->name ?? '-',
                        optional(optional($r->office)->municipality)->name ?? '-',
                        $r->status,
                        $r->payment_status,
                        number_format((float) $r->amount_paid, 2, '.', ''),
                        optional($r->created_at)->toDateTimeString() ?? '',
                        optional($r->completed_at)->toDateTimeString() ?? '',
                    ]),
            ),
            'payments' => $this->streamCsv(
                'payments-' . now()->format('Y-m-d') . '.csv',
                ['Reference', 'Citizen', 'Service', 'Office', 'Method', 'Transaction ID', 'Amount', 'Paid At'],
                ServiceRequest::with(['citizen:id,name', 'service:id,name', 'office:id,name'])
                    ->where('payment_status', 'paid')
                    ->orderByDesc('updated_at')
                    ->lazy()
                    ->map(fn ($r) => [
                        $r->reference_number,
                        optional($r->citizen)->name ?? '-',
                        optional($r->service)->name ?? '-',
                        optional($r->office)->name ?? '-',
                        $r->payment_method ?? '-',
                        $r->transaction_id ?? '-',
                        number_format((float) $r->amount_paid, 2, '.', ''),
                        optional($r->updated_at)->toDateTimeString() ?? '',
                    ]),
            ),
            'offices' => $this->streamCsv(
                'offices-' . now()->format('Y-m-d') . '.csv',
                ['Office', 'Municipality', 'Requests', 'Revenue (USD)'],
                Office::withCount('requests')
                    ->withSum(
                        ['requests as revenue' => fn ($q) => $q->where('payment_status', 'paid')],
                        'amount_paid'
                    )
                    ->with('municipality:id,name')
                    ->orderByDesc('requests_count')
                    ->lazy()
                    ->map(fn ($o) => [
                        $o->name,
                        optional($o->municipality)->name ?? '-',
                        $o->requests_count,
                        number_format((float) ($o->revenue ?? 0), 2, '.', ''),
                    ]),
            ),
            default => abort(404),
        };
    }

    private function streamCsv(string $filename, array $headers, iterable $rows): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM so Excel renders Arabic / accented characters correctly.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
        ]);
    }

    // ── Support Tickets ───────────────────────────────────────────
    public function supportIndex(Request $request)
    {
        $status = $request->input('status');
        $search = trim((string) $request->input('search', ''));

        $query = SupportTicket::query()->with('user:id,name,email');

        if (in_array($status, ['open', 'answered', 'closed'], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qq) use ($search) {
                      $qq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query
            ->withCount(['messages as unread_admin' => function ($q) {
                $q->where('sender_id', '!=', auth()->id())->whereNull('read_at');
            }])
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'answered' THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'open'     => SupportTicket::where('status', 'open')->count(),
            'answered' => SupportTicket::where('status', 'answered')->count(),
            'closed'   => SupportTicket::where('status', 'closed')->count(),
        ];

        return view('admin.support.index', compact('tickets', 'counts', 'status', 'search'));
    }

    public function supportShow(SupportTicket $ticket)
    {
        $ticket->load(['messages.sender', 'user']);

        $ticket->messages()
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('admin.support.show', compact('ticket'));
    }

    public function supportReply(Request $request, SupportTicket $ticket)
    {
        abort_if($ticket->status === 'closed', 403, 'This ticket is closed.');

        $data = $request->validate([
            'body'       => 'required|string|max:5000',
            'attachment' => 'nullable|file|max:5120|mimes:jpg,jpeg,png,gif,pdf,doc,docx,txt',
        ]);

        $attachmentData = [];
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentData = [
                'attachment'      => $file->store('support-attachments', 'public'),
                'attachment_name' => $file->getClientOriginalName(),
                'attachment_size' => $file->getSize(),
            ];
        }

        $newMessage = DB::transaction(function () use ($data, $ticket, $attachmentData) {
            $msg = SupportTicketMessage::create(array_merge([
                'support_ticket_id' => $ticket->id,
                'sender_id' => auth()->id(),
                'body'      => $data['body'],
            ], $attachmentData));

            $ticket->update([
                'status' => 'answered',
                'last_reply_at' => now(),
            ]);

            return $msg;
        });

        broadcast(new SupportTicketMessageSent($newMessage))->toOthers();

        $ticket->user->notify(new SupportTicketReplyNotification(
            $ticket,
            Str::limit($data['body'], 140),
            auth()->user()->name,
            'admin',
        ));

        return back()->with('success', 'Reply sent to citizen.');
    }

    public function supportClose(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'closed']);
        return back()->with('success', 'Ticket closed.');
    }

    public function supportReopen(SupportTicket $ticket)
    {
        $ticket->update(['status' => 'answered']);
        return back()->with('success', 'Ticket reopened.');
    }
}
