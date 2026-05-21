@extends('layouts.app')
@section('title','Municipalities')
@section('page-title','Municipalities')

@push('styles')
<style>
    /* ═══ ADMIN MUNICIPALITIES — PREMIUM GLASS ═══ */
    .admin-muted { color: var(--es-muted); }
    .admin-count-chip {
        background: linear-gradient(135deg, #4F46E5, #2563EB);
        color: #fff;
        border: none;
        padding: .2rem .6rem;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(79,70,229,0.2);
    }
    .admin-mobile-item {
        padding: .9rem 1rem;
        border-bottom: 1px solid rgba(226,232,240,0.5);
        transition: background .22s ease, transform .22s ease;
    }
    .admin-mobile-item:hover {
        background: rgba(224,231,255,0.12);
        transform: translateX(4px);
    }
    .admin-empty { text-align: center; padding: 2rem; color: var(--es-muted); }

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

    @media (prefers-reduced-motion: reduce) {
        .admin-mobile-item { transition: none; }
    }
</style>
@endpush

@section('content')
<x-admin.page-header title="Manage Municipalities" :subtitle="$municipalities->total() . ' total'" class="admin-reveal">
    <x-slot:actions>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i> Add Municipality
        </button>
    </x-slot:actions>
</x-admin.page-header>

<div class="card admin-reveal admin-busy-target" id="adminMunicipalitiesTableCard">
    <div class="card-body" style="padding:0 !important">
        <x-admin.table-toolbar
            title="Municipality Directory"
            subtitle="Track regions, active status, and office coverage.">
            <x-slot:actions>
                <div class="admin-density-switch">
                    <button
                        type="button"
                        class="admin-density-btn is-active"
                        data-admin-density-target="#adminMunicipalitiesTable"
                        data-admin-density="comfortable">Comfort</button>
                    <button
                        type="button"
                        class="admin-density-btn"
                        data-admin-density-target="#adminMunicipalitiesTable"
                        data-admin-density="compact">Compact</button>
                </div>
            </x-slot:actions>
        </x-admin.table-toolbar>

        <div class="admin-chip-filters" data-admin-filter-group>
            <button type="button" class="admin-chip-filter is-active" data-admin-table-filter data-admin-table-filter-target="#adminMunicipalitiesTable" data-admin-filter-field="status" data-admin-filter-value="all">All</button>
            <button type="button" class="admin-chip-filter" data-admin-table-filter data-admin-table-filter-target="#adminMunicipalitiesTable" data-admin-filter-field="status" data-admin-filter-value="active">Active</button>
            <button type="button" class="admin-chip-filter" data-admin-table-filter data-admin-table-filter-target="#adminMunicipalitiesTable" data-admin-filter-field="status" data-admin-filter-value="inactive">Inactive</button>
        </div>

        <div class="d-none d-md-block admin-table-wrap">
            <table id="adminMunicipalitiesTable" class="table table-hover admin-table-sticky admin-table-interactive" data-admin-table>
                <thead>
                    <tr>
                        <th data-sort="0" data-sort-type="text">Name</th>
                        <th data-sort="1" data-sort-type="text">Region</th>
                        <th data-sort="2" data-sort-type="text">Country</th>
                        <th data-sort="3" data-sort-type="number">Offices</th>
                        <th data-sort="4" data-sort-type="text">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($municipalities as $m)
                    <tr data-status="{{ $m->is_active ? 'active' : 'inactive' }}">
                        <td style="font-weight:600">{{ $m->name }}</td>
                        <td class="admin-muted">{{ $m->region ?? '-' }}</td>
                        <td class="admin-muted">{{ $m->country }}</td>
                        <td><span class="admin-count-chip">{{ $m->offices_count }}</span></td>
                        <td><span class="sbadge {{ $m->is_active ? 's-approved' : 's-rejected' }}">{{ $m->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm admin-icon-btn"
                                        onclick="editMunicipality({{ $m->id }}, {{ Js::from($m->name) }}, {{ Js::from($m->region) }}, {{ $m->is_active ? 1 : 0 }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form
                                    action="{{ route('admin.municipalities.destroy', $m) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete {{ $m->name }}?')"
                                    data-admin-busy-target="#adminMunicipalitiesTableCard">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm admin-trash-btn"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="admin-empty">No municipalities yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-md-none">
            @forelse($municipalities as $m)
            <div class="admin-mobile-item">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div>
                        <div style="font-weight:700;font-size:.88rem;color:#566A7F">{{ $m->name }}</div>
                        <div class="admin-muted" style="font-size:.75rem">{{ $m->region ?? 'No region' }} &middot; {{ $m->offices_count }} offices</div>
                    </div>
                    <div style="display:flex;gap:.4rem;align-items:center">
                        <span class="sbadge {{ $m->is_active ? 's-approved' : 's-rejected' }}">{{ $m->is_active ? 'Active' : 'Inactive' }}</span>
                        <button class="btn btn-sm admin-icon-btn"
                                onclick="editMunicipality({{ $m->id }}, {{ Js::from($m->name) }}, {{ Js::from($m->region) }}, {{ $m->is_active ? 1 : 0 }})">
                            <i class="bi bi-pencil" style="font-size:.8rem"></i>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="admin-empty"><i class="bi bi-geo-alt" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#c2cddd"></i>No municipalities yet.</div>
            @endforelse
        </div>
        @if($municipalities->hasPages())
        <div style="padding:.75rem 1rem;border-top:1px solid var(--es-border-soft)">{{ $municipalities->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade admin-modal" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add Municipality</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.municipalities.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Region</label><input type="text" name="region" class="form-control" placeholder="e.g. Mount Lebanon"></div>
                    <div><label class="form-label">Country</label><input type="text" name="country" class="form-control" value="Lebanon"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm admin-plain-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Municipality</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade admin-modal" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Municipality</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" id="editName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Region</label><input type="text" name="region" id="editRegion" class="form-control"></div>
                    <div><label class="form-label">Status</label>
                        <select name="is_active" class="form-select" id="editStatus">
                            <option value="1">Active</option><option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm admin-plain-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editMunicipality(id, name, region, active) {
    document.getElementById('editForm').action = `/admin/municipalities/${id}`;
    document.getElementById('editName').value   = name;
    document.getElementById('editRegion').value = region || '';
    document.getElementById('editStatus').value = active;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

(function () {
    const url = new URL(window.location.href);
    if (url.searchParams.get('quick') !== 'add') return;
    const quickModalEl = document.getElementById('addModal');
    if (!quickModalEl || typeof bootstrap === 'undefined') return;

    bootstrap.Modal.getOrCreateInstance(quickModalEl).show();
    url.searchParams.delete('quick');
    const nextQuery = url.searchParams.toString();
    history.replaceState({}, '', `${url.pathname}${nextQuery ? `?${nextQuery}` : ''}${url.hash}`);
})();
</script>
@endpush
@endsection

