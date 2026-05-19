@extends('layouts.app')
@section('title','Users')
@section('page-title','User Management')

@push('styles')
<style>
    /* ═══ ADMIN USERS — PREMIUM GLASS ═══ */
    .admin-filter-form { display: flex; flex-wrap: wrap; gap: .6rem; align-items: flex-end; }
    .admin-filter-search { flex: 1; min-width: 160px; }
    .admin-filter-role { min-width: 140px; }
    .admin-filter-verification { min-width: 180px; }
    .admin-plain-btn { margin-top: auto; }

    /* Avatars — gradient */
    .admin-avatar {
        width: 34px; height: 34px; border-radius: 50%;
        background: linear-gradient(135deg, #4F46E5, #2563EB);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font-size: .78rem; font-weight: 700; flex-shrink: 0; border: none;
        box-shadow: 0 3px 8px rgba(79,70,229,0.18);
    }
    .admin-avatar.mobile { width: 38px; height: 38px; font-size: .85rem; }

    .admin-row-name { font-weight: 600; font-size: .83rem; }
    .admin-row-email { font-size: .72rem; color: var(--es-muted); }

    /* Role chips — glass with color coding */
    .admin-role-chip {
        padding: .22rem .65rem; border-radius: 20px;
        font-size: .7rem; font-weight: 600; border: 1px solid transparent;
        backdrop-filter: blur(4px);
    }
    .admin-role-admin {
        background: linear-gradient(135deg, rgba(238,242,255,0.7), rgba(224,231,255,0.7));
        color: #4338CA; border-color: rgba(199,210,254,0.5);
    }
    .admin-role-office_user {
        background: linear-gradient(135deg, rgba(220,252,231,0.7), rgba(209,250,229,0.7));
        color: #15803D; border-color: rgba(187,247,208,0.5);
    }
    .admin-role-citizen {
        background: linear-gradient(135deg, rgba(219,234,254,0.7), rgba(191,219,254,0.7));
        color: var(--es-primary); border-color: rgba(147,197,253,0.4);
    }
    .admin-role-default {
        background: rgba(238,242,247,0.6); color: #475569; border-color: rgba(221,229,239,0.5);
    }

    .admin-date { font-size: .78rem; color: var(--es-muted); }
    .admin-identity-cell { min-width: 190px; }
    .admin-identity-meta { margin-top: .25rem; font-size: .68rem; color: var(--es-muted); }
    .admin-action-row { display: flex; flex-wrap: wrap; gap: .35rem; align-items: center; }
    .sbadge.s-pending {
        background: rgba(255,251,235,0.78);
        border-color: rgba(251,191,36,0.35);
        color: #B45309;
    }

    /* Action buttons — glass with hover gradient */
    .admin-action-danger {
        background: rgba(254,226,226,0.6); backdrop-filter: blur(4px);
        border: 1px solid rgba(254,202,202,0.5); color: #DC2626;
        transition: all .22s ease;
    }
    .admin-action-danger:hover {
        background: #DC2626; color: #fff; border-color: transparent;
        box-shadow: 0 4px 12px rgba(220,38,38,0.2);
    }
    .admin-action-success {
        background: rgba(209,250,229,0.6); backdrop-filter: blur(4px);
        border: 1px solid rgba(167,243,208,0.5); color: #059669;
        transition: all .22s ease;
    }
    .admin-action-success:hover {
        background: #059669; color: #fff; border-color: transparent;
        box-shadow: 0 4px 12px rgba(5,150,105,0.2);
    }
    .admin-action-secondary {
        background: rgba(219,234,254,0.6); backdrop-filter: blur(4px);
        border: 1px solid rgba(147,197,253,0.45); color: #2563EB;
        transition: all .22s ease;
    }
    .admin-action-secondary:hover {
        background: #2563EB; color: #fff; border-color: transparent;
        box-shadow: 0 4px 12px rgba(37,99,235,0.18);
    }
    .admin-review-modal .modal-dialog { max-width: 780px; }
    .admin-review-grid {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 1rem;
    }
    .admin-review-panel {
        border: 1px solid rgba(226,232,240,0.72);
        border-radius: .75rem;
        padding: .9rem;
        background: rgba(248,250,252,0.58);
    }
    .admin-review-panel-title {
        font-size: .78rem;
        font-weight: 800;
        color: #334155;
        margin-bottom: .7rem;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .admin-review-row {
        display: flex;
        justify-content: space-between;
        gap: .8rem;
        padding: .46rem 0;
        border-bottom: 1px solid rgba(226,232,240,0.62);
        font-size: .78rem;
    }
    .admin-review-row:last-child { border-bottom: 0; }
    .admin-review-label { color: #94A3B8; font-weight: 600; }
    .admin-review-value { color: #0F172A; font-weight: 700; text-align: right; word-break: break-word; }
    @media (max-width: 767.98px) {
        .admin-review-grid { grid-template-columns: 1fr; }
    }

    .admin-empty { text-align: center; padding: 2rem; color: var(--es-muted); }

    /* Mobile items — glass hover */
    .admin-mobile-item {
        padding: .9rem 1rem;
        border-bottom: 1px solid rgba(226,232,240,0.5);
        display: flex; align-items: center; gap: .75rem;
        transition: background .22s ease, transform .22s ease;
    }
    .admin-mobile-item:hover {
        background: rgba(224,231,255,0.12);
        transform: translateX(4px);
    }
    .admin-mobile-name {
        font-weight: 700; font-size: .85rem;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }

    /* Modals — glass */
    .admin-modal .modal-dialog { max-width: 440px; }
    .admin-modal .modal-content {
        border: 1px solid rgba(79,70,229,0.08) !important;
        border-radius: .9rem;
        background: rgba(255,255,255,0.85) !important;
        backdrop-filter: blur(20px) saturate(1.6);
        -webkit-backdrop-filter: blur(20px) saturate(1.6);
        box-shadow: 0 24px 64px rgba(15,23,42,0.18);
    }
    .admin-modal .modal-header { border: none; padding: 1.1rem 1.25rem .45rem; }
    .admin-modal .modal-title { font-weight: 700; color: #566A7F; }
    .admin-modal .modal-body { padding: .75rem 1.25rem; }
    .admin-modal .modal-footer { border: none; padding: .75rem 1.25rem 1.1rem; gap: .5rem; }

    /* Bulk select */
    .admin-select-cell { width: 34px; text-align: center; }
    .admin-select-input { cursor: pointer; width: 1rem; height: 1rem; }
    .admin-bulk-bar { display: none; align-items: center; gap: .5rem; }
    .admin-bulk-bar.show { display: inline-flex; }
    .admin-bulk-count { font-size: .76rem; color: #566A7F; font-weight: 600; margin-right: .2rem; }

    @media (prefers-reduced-motion: reduce) {
        .admin-action-danger, .admin-action-success, .admin-mobile-item { transition: none; }
    }
</style>
@endpush

@section('content')
<x-admin.page-header title="Manage Users" :subtitle="$users->total() . ' total'">
    <x-slot:actions>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus"></i> Add Office User
        </button>
    </x-slot:actions>
</x-admin.page-header>

{{-- Filters --}}
<div class="card mb-3 admin-reveal">
    <div class="card-body">
        <form method="GET" class="admin-filter-form" data-admin-busy-target="#adminUsersTableCard">
            <div class="admin-filter-search">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name or email..." value="{{ request('search') }}">
            </div>
            <div class="admin-filter-role">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">All Roles</option>
                    <option value="citizen"     {{ request('role')==='citizen'     ? 'selected':'' }}>Citizen</option>
                    <option value="office_user" {{ request('role')==='office_user' ? 'selected':'' }}>Office User</option>
                    <option value="admin"       {{ request('role')==='admin'       ? 'selected':'' }}>Admin</option>
                </select>
            </div>
            <div class="admin-filter-verification">
                <label class="form-label">Citizen Identity</label>
                <select name="verification_status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" {{ request('verification_status')==='pending' ? 'selected':'' }}>Pending approval</option>
                    <option value="approved" {{ request('verification_status')==='approved' ? 'selected':'' }}>Approved</option>
                    <option value="rejected" {{ request('verification_status')==='rejected' ? 'selected':'' }}>Rejected</option>
                    <option value="missing_document" {{ request('verification_status')==='missing_document' ? 'selected':'' }}>Missing document</option>
                </select>
            </div>
            <button class="btn btn-primary btn-sm" style="margin-top:auto"><i class="bi bi-funnel"></i> Filter</button>
            @if(request()->hasAny(['search','role','verification_status']))
                <a href="{{ route('admin.users') }}" class="btn btn-sm admin-plain-btn" data-admin-busy-target="#adminUsersTableCard">Clear</a>
            @endif
        </form>
    </div>
</div>

<div class="card admin-reveal admin-busy-target" id="adminUsersTableCard">
    <div class="card-body" style="padding:0 !important">
        <x-admin.table-toolbar title="User Directory" subtitle="Select multiple users to apply bulk status actions.">
            <x-slot:actions>
                <div id="adminUserBulkBar" class="admin-bulk-bar">
                    <span id="adminUserBulkCount" class="admin-bulk-count">0 selected</span>
                    <button type="button" id="adminUserBulkActivate" class="btn btn-sm admin-action-success">
                        <i class="bi bi-check2-circle"></i> Activate
                    </button>
                    <button type="button" id="adminUserBulkDeactivate" class="btn btn-sm admin-action-danger">
                        <i class="bi bi-x-circle"></i> Deactivate
                    </button>
                    <button type="button" id="adminUserBulkClear" class="btn btn-sm admin-plain-btn">
                        Clear
                    </button>
                </div>
                <div class="admin-density-switch">
                    <button
                        type="button"
                        class="admin-density-btn is-active"
                        data-admin-density-target="#adminUsersTable"
                        data-admin-density="comfortable">Comfort</button>
                    <button
                        type="button"
                        class="admin-density-btn"
                        data-admin-density-target="#adminUsersTable"
                        data-admin-density="compact">Compact</button>
                </div>
            </x-slot:actions>
        </x-admin.table-toolbar>

        <div class="admin-chip-filters" data-admin-filter-group>
            <button type="button" class="admin-chip-filter is-active" data-admin-table-filter data-admin-table-filter-target="#adminUsersTable" data-admin-filter-field="status" data-admin-filter-value="all">All</button>
            <button type="button" class="admin-chip-filter" data-admin-table-filter data-admin-table-filter-target="#adminUsersTable" data-admin-filter-field="status" data-admin-filter-value="active">Active</button>
            <button type="button" class="admin-chip-filter" data-admin-table-filter data-admin-table-filter-target="#adminUsersTable" data-admin-filter-field="status" data-admin-filter-value="inactive">Inactive</button>
        </div>

        <div class="d-none d-md-block admin-table-wrap">
            <table id="adminUsersTable" class="table table-hover admin-table-sticky admin-table-interactive" data-admin-table>
                <thead>
                    <tr>
                        <th class="admin-select-cell">
                            <input type="checkbox" id="adminUsersSelectAll" class="form-check-input admin-select-input" aria-label="Select all users">
                        </th>
                        <th data-sort="1" data-sort-type="text">User</th>
                        <th data-sort="2" data-sort-type="text">Role</th>
                        <th data-sort="3" data-sort-type="text">Status</th>
                        <th data-sort="4" data-sort-type="text">Identity</th>
                        <th data-sort="5" data-sort-type="date">Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr
                        data-user-id="{{ $user->id }}"
                        data-user-active="{{ $user->is_active ? 1 : 0 }}"
                        data-status="{{ $user->is_active ? 'active' : 'inactive' }}"
                        data-role="{{ $user->role }}"
                        data-toggle-url="{{ route('admin.users.toggle', $user) }}">
                        <td class="admin-select-cell">
                            @if($user->id !== auth()->id())
                                <input type="checkbox" class="form-check-input admin-select-input js-user-select" aria-label="Select user {{ $user->name }}">
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:.65rem">
                                <div class="admin-avatar">
                                    {{ strtoupper(substr($user->name,0,1)) }}
                                </div>
                                <div>
                                    <div class="admin-row-name">{{ $user->name }}</div>
                                    <div class="admin-row-email">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleClass = match($user->role) {
                                    'admin' => 'admin-role-admin',
                                    'office_user' => 'admin-role-office_user',
                                    'citizen' => 'admin-role-citizen',
                                    default => 'admin-role-default',
                                };
                            @endphp
                            <span class="admin-role-chip {{ $roleClass }}">
                                {{ ucfirst(str_replace('_',' ',$user->role)) }}
                            </span>
                        </td>
                        <td><span class="sbadge {{ $user->is_active ? 's-approved' : 's-rejected' }}">{{ $user->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            @if($user->isCitizen())
                                @php
                                    $identityStatus = $user->id_document ? ($user->citizen_verification_status ?? 'pending') : 'missing';
                                    $identityBadgeClass = match($identityStatus) {
                                        'approved' => 's-approved',
                                        'rejected', 'missing' => 's-rejected',
                                        default => 's-pending',
                                    };
                                    $identityLabel = $identityStatus === 'missing' ? 'No document' : ucfirst($identityStatus);
                                @endphp
                                <div class="admin-identity-cell">
                                    <span class="sbadge {{ $identityBadgeClass }}">{{ $identityLabel }}</span>
                                    <div class="admin-identity-meta">ID: {{ $user->national_id ?: '-' }}</div>
                                    @if($user->citizen_verified_at)
                                        <div class="admin-identity-meta">By {{ $user->citizenVerifier?->name ?? 'admin' }} on {{ $user->citizen_verified_at->format('M d, Y') }}</div>
                                    @elseif($user->citizen_verification_notes)
                                        <div class="admin-identity-meta">{{ \Illuminate\Support\Str::limit($user->citizen_verification_notes, 48) }}</div>
                                    @endif
                                </div>
                            @else
                                <span class="admin-row-email">Not required</span>
                            @endif
                        </td>
                        <td class="admin-date" data-sort-value="{{ $user->created_at->timestamp }}">{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="admin-action-row">
                                @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="js-user-toggle-form">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $user->is_active ? 'admin-action-danger' : 'admin-action-success' }}">
                                        <i class="bi bi-{{ $user->is_active ? 'person-x' : 'person-check' }}"></i>
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                @else
                                <span class="admin-row-email">Current user</span>
                                @endif

                                @if($user->isCitizen())
                                    <button type="button" class="btn btn-sm admin-action-secondary" data-bs-toggle="modal" data-bs-target="#reviewCitizenModal{{ $user->id }}">
                                        <i class="bi bi-card-checklist"></i> Review / Edit
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="admin-empty">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-md-none">
            @forelse($users as $user)
            <div class="admin-mobile-item">
                <div class="admin-avatar mobile">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <div class="admin-mobile-name">{{ $user->name }}</div>
                    <div class="admin-row-email">{{ $user->email }}</div>
                    <div style="margin-top:3px">
                        <span class="admin-row-email" style="font-weight:600">{{ ucfirst(str_replace('_',' ',$user->role)) }}</span>
                        @if($user->isCitizen())
                            @php
                                $mobileIdentityStatus = $user->id_document ? ($user->citizen_verification_status ?? 'pending') : 'missing';
                                $mobileIdentityClass = match($mobileIdentityStatus) {
                                    'approved' => 's-approved',
                                    'rejected', 'missing' => 's-rejected',
                                    default => 's-pending',
                                };
                            @endphp
                            <span class="sbadge {{ $mobileIdentityClass }}" style="margin-left:.35rem">
                                {{ $mobileIdentityStatus === 'missing' ? 'No document' : ucfirst($mobileIdentityStatus) }}
                            </span>
                        @endif
                    </div>
                </div>
                <span class="sbadge {{ $user->is_active ? 's-approved' : 's-rejected' }}">{{ $user->is_active ? 'Active' : 'Off' }}</span>
                @if($user->isCitizen())
                    <button type="button" class="btn btn-sm admin-action-secondary" data-bs-toggle="modal" data-bs-target="#reviewCitizenModal{{ $user->id }}">
                        Review
                    </button>
                @endif
            </div>
            @empty
            <div class="admin-empty">No users found.</div>
            @endforelse
        </div>
        @if($users->hasPages())
        <div style="padding:.75rem 1rem;border-top:1px solid var(--es-border-soft)">{{ $users->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Office User Modal --}}
<div class="modal fade admin-modal" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Create Office User</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.office.create') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" minlength="8" required></div>
                    <div>
                        <label class="form-label">Assign to Office *</label>
                        <select name="office_id" class="form-select" required>
                            <option value="">Select office...</option>
                            @foreach(\App\Models\Office::where('is_active',true)->with('municipality')->get() as $office)
                                <option value="{{ $office->id }}">{{ $office->name }} ({{ $office->municipality->name }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm admin-plain-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Citizen Review / Edit Modals --}}
@foreach($users as $reviewUser)
    @if($reviewUser->isCitizen())
        @php
            $reviewIdentityStatus = $reviewUser->id_document ? ($reviewUser->citizen_verification_status ?? 'pending') : 'missing';
            $reviewIdentityClass = match($reviewIdentityStatus) {
                'approved' => 's-approved',
                'rejected', 'missing' => 's-rejected',
                default => 's-pending',
            };
            $reviewIdentityLabel = $reviewIdentityStatus === 'missing' ? 'No document' : ucfirst($reviewIdentityStatus);
            $reviewFormId = 'reviewCitizenForm' . $reviewUser->id;
        @endphp
        <div class="modal fade admin-modal admin-review-modal" id="reviewCitizenModal{{ $reviewUser->id }}" tabindex="-1" aria-labelledby="reviewCitizenTitle{{ $reviewUser->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title" id="reviewCitizenTitle{{ $reviewUser->id }}">Review Citizen Identity</h6>
                            <div class="admin-row-email">{{ $reviewUser->email }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="admin-review-grid">
                            <div class="admin-review-panel">
                                <div class="admin-review-panel-title">Submitted Information</div>
                                <div class="admin-review-row">
                                    <span class="admin-review-label">Full name</span>
                                    <span class="admin-review-value">{{ $reviewUser->name }}</span>
                                </div>
                                <div class="admin-review-row">
                                    <span class="admin-review-label">Email</span>
                                    <span class="admin-review-value">{{ $reviewUser->email }}</span>
                                </div>
                                <div class="admin-review-row">
                                    <span class="admin-review-label">Phone</span>
                                    <span class="admin-review-value">{{ $reviewUser->phone ?: '-' }}</span>
                                </div>
                                <div class="admin-review-row">
                                    <span class="admin-review-label">National ID</span>
                                    <span class="admin-review-value">{{ $reviewUser->national_id ?: '-' }}</span>
                                </div>
                                <div class="admin-review-row">
                                    <span class="admin-review-label">Identity status</span>
                                    <span class="admin-review-value"><span class="sbadge {{ $reviewIdentityClass }}">{{ $reviewIdentityLabel }}</span></span>
                                </div>
                                <div class="admin-review-row">
                                    <span class="admin-review-label">Joined</span>
                                    <span class="admin-review-value">{{ $reviewUser->created_at->format('M d, Y H:i') }}</span>
                                </div>
                                <div class="mt-3">
                                    @if($reviewUser->id_document)
                                        <a href="{{ route('admin.users.identity.document', $reviewUser) }}" target="_blank" class="btn btn-sm admin-action-secondary">
                                            <i class="bi bi-file-earmark-person"></i> Open ID Document
                                        </a>
                                    @else
                                        <span class="sbadge s-rejected">No ID document uploaded</span>
                                    @endif
                                </div>
                            </div>

                            <div class="admin-review-panel">
                                <div class="admin-review-panel-title">Edit Citizen Information</div>
                                <form id="{{ $reviewFormId }}" action="{{ route('admin.users.update', $reviewUser) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="mb-2">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ old('name', $reviewUser->name) }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $reviewUser->email) }}" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $reviewUser->phone) }}" placeholder="+961...">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">National ID Number</label>
                                        <input type="text" name="national_id" class="form-control" value="{{ old('national_id', $reviewUser->national_id) }}">
                                    </div>
                                    <div>
                                        <label class="form-label">Admin Note</label>
                                        <textarea name="citizen_verification_notes" class="form-control" rows="3" placeholder="Reason for rejection or correction note">{{ old('citizen_verification_notes', $reviewUser->citizen_verification_notes) }}</textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm admin-plain-btn" data-bs-dismiss="modal">Close</button>
                        <button type="submit" form="{{ $reviewFormId }}" class="btn btn-sm admin-action-secondary">
                            <i class="bi bi-save"></i> Save Info
                        </button>
                        @if($reviewUser->id_document && !$reviewUser->isCitizenIdentityRejected())
                            <form action="{{ route('admin.users.identity.reject', $reviewUser) }}" method="POST" onsubmit="return confirm('Reject this citizen identity?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm admin-action-danger">
                                    <i class="bi bi-x-octagon"></i> Reject
                                </button>
                            </form>
                        @endif
                        @if($reviewUser->id_document && !$reviewUser->isCitizenIdentityApproved())
                            <form action="{{ route('admin.users.identity.approve', $reviewUser) }}" method="POST" onsubmit="return confirm('Approve this citizen identity?')">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm admin-action-success">
                                    <i class="bi bi-patch-check"></i> Approve
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

@push('scripts')
<script>
    (function () {
        const url = new URL(window.location.href);
        if (url.searchParams.get('quick') === 'add') {
            const quickModalEl = document.getElementById('addUserModal');
            if (quickModalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(quickModalEl).show();
                url.searchParams.delete('quick');
                const nextQuery = url.searchParams.toString();
                history.replaceState({}, '', `${url.pathname}${nextQuery ? `?${nextQuery}` : ''}${url.hash}`);
            }
        }

        const table = document.querySelector('.admin-table-sticky');
        if (!table) return;

        const selectAll = document.getElementById('adminUsersSelectAll');
        const rowCheckboxes = Array.from(document.querySelectorAll('.js-user-select'));
        const bulkBar = document.getElementById('adminUserBulkBar');
        const bulkCount = document.getElementById('adminUserBulkCount');
        const bulkActivateBtn = document.getElementById('adminUserBulkActivate');
        const bulkDeactivateBtn = document.getElementById('adminUserBulkDeactivate');
        const bulkClearBtn = document.getElementById('adminUserBulkClear');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const selectedRows = () => rowCheckboxes
            .filter((cb) => cb.checked)
            .map((cb) => cb.closest('tr'))
            .filter(Boolean);

        const refreshBulkState = () => {
            const selected = selectedRows();
            const selectedCount = selected.length;
            if (bulkCount) {
                bulkCount.textContent = `${selectedCount} selected`;
            }
            if (bulkBar) {
                bulkBar.classList.toggle('show', selectedCount > 0);
            }
            if (selectAll) {
                const allChecked = rowCheckboxes.length > 0 && rowCheckboxes.every((cb) => cb.checked);
                selectAll.checked = allChecked;
                selectAll.indeterminate = selectedCount > 0 && !allChecked;
            }
        };

        const setBusy = (busy) => {
            [bulkActivateBtn, bulkDeactivateBtn, bulkClearBtn, selectAll, ...rowCheckboxes].forEach((el) => {
                if (!el) return;
                el.disabled = busy;
            });
        };

        const bulkToggle = async (targetActive) => {
            const rows = selectedRows();
            if (!rows.length) return;

            const targets = rows.filter((row) => Number(row.dataset.userActive) !== targetActive);
            if (!targets.length) {
                refreshBulkState();
                return;
            }

            setBusy(true);
            try {
                await Promise.all(targets.map((row) =>
                    fetch(row.dataset.toggleUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: '_method=PATCH'
                    })
                ));
                window.location.reload();
            } catch (error) {
                console.error(error);
                alert('Bulk update failed. Please try again.');
                setBusy(false);
            }
        };

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                rowCheckboxes.forEach((cb) => {
                    cb.checked = selectAll.checked;
                });
                refreshBulkState();
            });
        }

        rowCheckboxes.forEach((cb) => {
            cb.addEventListener('change', refreshBulkState);
        });

        bulkActivateBtn?.addEventListener('click', () => bulkToggle(1));
        bulkDeactivateBtn?.addEventListener('click', () => bulkToggle(0));
        bulkClearBtn?.addEventListener('click', () => {
            rowCheckboxes.forEach((cb) => {
                cb.checked = false;
            });
            refreshBulkState();
        });

        refreshBulkState();
    })();
</script>
@endpush
@endsection
