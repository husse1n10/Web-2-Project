@extends('layouts.app')
@section('title','Offices')
@section('page-title','Government Offices')

@push('styles')
<style>
    /* ═══ ADMIN OFFICES — PREMIUM GLASS ═══ */
    .admin-row-main { font-weight: 600; font-size: .84rem; }
    .admin-row-sub { font-size: .72rem; color: var(--es-muted); }
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
    .admin-modal .modal-dialog { max-width: 480px; }
    .admin-modal.narrow .modal-dialog { max-width: 440px; }
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
    .admin-modal.office-location .modal-dialog { max-width: 720px; }
    .admin-office-map {
        height: 260px;
        border-radius: .75rem;
        border: 1px solid rgba(148,163,184,0.38);
        overflow: hidden;
        background: rgba(241,245,249,0.72);
    }
    .admin-map-help {
        display: flex;
        align-items: center;
        gap: .45rem;
        margin-top: .45rem;
        color: var(--es-muted);
        font-size: .72rem;
    }
    .admin-map-unavailable {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 1rem;
        text-align: center;
        color: var(--es-muted);
        font-size: .8rem;
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-mobile-item { transition: none; }
    }
</style>
@endpush

@section('content')
<x-admin.page-header title="Manage Offices" :subtitle="$offices->total() . ' total'" class="admin-reveal">
    <x-slot:actions>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addOfficeModal">
            <i class="bi bi-plus-lg"></i> Add Office
        </button>
    </x-slot:actions>
</x-admin.page-header>

<div class="card admin-reveal admin-busy-target" id="adminOfficesTableCard">
    <div class="card-body" style="padding:0 !important">
        <x-admin.table-toolbar
            title="Office Directory"
            subtitle="Keep municipal offices updated with location and contact details.">
            <x-slot:actions>
                <div class="admin-density-switch">
                    <button
                        type="button"
                        class="admin-density-btn is-active"
                        data-admin-density-target="#adminOfficesTable"
                        data-admin-density="comfortable">Comfort</button>
                    <button
                        type="button"
                        class="admin-density-btn"
                        data-admin-density-target="#adminOfficesTable"
                        data-admin-density="compact">Compact</button>
                </div>
            </x-slot:actions>
        </x-admin.table-toolbar>

        <div class="admin-chip-filters" data-admin-filter-group>
            <button type="button" class="admin-chip-filter is-active" data-admin-table-filter data-admin-table-filter-target="#adminOfficesTable" data-admin-filter-field="status" data-admin-filter-value="all">All</button>
            <button type="button" class="admin-chip-filter" data-admin-table-filter data-admin-table-filter-target="#adminOfficesTable" data-admin-filter-field="status" data-admin-filter-value="active">Active</button>
            <button type="button" class="admin-chip-filter" data-admin-table-filter data-admin-table-filter-target="#adminOfficesTable" data-admin-filter-field="status" data-admin-filter-value="inactive">Inactive</button>
        </div>

        <div class="d-none d-md-block admin-table-wrap">
            <table id="adminOfficesTable" class="table table-hover admin-table-sticky admin-table-interactive" data-admin-table>
                <thead>
                    <tr>
                        <th data-sort="0" data-sort-type="text">Office Name</th>
                        <th data-sort="1" data-sort-type="text">Municipality</th>
                        <th data-sort="2" data-sort-type="text">Contact</th>
                        <th data-sort="3" data-sort-type="text">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($offices as $office)
                    <tr data-status="{{ $office->is_active ? 'active' : 'inactive' }}">
                        <td>
                            <div class="admin-row-main">{{ $office->name }}</div>
                            <div class="admin-row-sub">{{ $office->address }}</div>
                        </td>
                        <td class="admin-row-sub">{{ $office->municipality->name }}</td>
                        <td>
                            <div style="font-size:.78rem">{{ $office->phone ?? '-' }}</div>
                            <div class="admin-row-sub">{{ $office->email ?? '' }}</div>
                        </td>
                        <td><span class="sbadge {{ $office->is_active ? 's-approved' : 's-rejected' }}">{{ $office->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <button
                                    type="button"
                                    class="btn btn-sm admin-icon-btn js-edit-office"
                                    data-office-id="{{ $office->id }}"
                                    data-office-name="{{ $office->name }}"
                                    data-office-municipality="{{ $office->municipality_id }}"
                                    data-office-address="{{ $office->address }}"
                                    data-office-latitude="{{ $office->latitude }}"
                                    data-office-longitude="{{ $office->longitude }}"
                                    data-office-phone="{{ $office->phone }}"
                                    data-office-email="{{ $office->email }}"
                                    data-office-active="{{ $office->is_active ? 1 : 0 }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form
                                    action="{{ route('admin.offices.destroy', $office) }}"
                                    method="POST"
                                    onsubmit="return confirm('Delete this office?')"
                                    data-admin-busy-target="#adminOfficesTableCard">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm admin-trash-btn"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="admin-empty">No offices yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-md-none">
            @forelse($offices as $office)
            <div class="admin-mobile-item">
                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                    <div style="min-width:0;flex:1">
                        <div style="font-weight:700;font-size:.88rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#566A7F">{{ $office->name }}</div>
                        <div class="admin-row-sub" style="font-size:.74rem">{{ $office->municipality->name }}</div>
                        <div class="admin-row-sub" style="font-size:.73rem;margin-top:2px">{{ $office->phone ?? 'No phone' }}</div>
                    </div>
                    <span class="sbadge {{ $office->is_active ? 's-approved' : 's-rejected' }}" style="margin-left:.75rem;flex-shrink:0">{{ $office->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>
            @empty
            <div class="admin-empty"><i class="bi bi-building" style="font-size:2rem;display:block;margin-bottom:.5rem;color:#c2cddd"></i>No offices yet.</div>
            @endforelse
        </div>
        @if($offices->hasPages())
        <div style="padding:.75rem 1rem;border-top:1px solid var(--es-border-soft)">{{ $offices->links() }}</div>
        @endif
    </div>
</div>

{{-- Add Office Modal --}}
<div class="modal fade admin-modal office-location" id="addOfficeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add Government Office</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.offices.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Municipality *</label>
                        <select name="municipality_id" class="form-select" required>
                            <option value="">Select municipality...</option>
                            @foreach(\App\Models\Municipality::where('is_active',true)->get() as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Office Name *</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Address *</label><input type="text" name="address" class="form-control" required></div>
                    <div class="mb-3">
                        <label class="form-label">Location on Map</label>
                        <div id="addOfficeMap" class="admin-office-map">
                            <div class="admin-map-unavailable">Loading map...</div>
                        </div>
                        <div class="admin-map-help">
                            <i class="bi bi-pin-map"></i>
                            Click the map to place the office pin. Latitude and longitude will update automatically.
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:.75rem">
                        <div><label class="form-label">Latitude</label><input type="text" id="addOfficeLat" name="latitude" class="form-control" placeholder="33.8938"></div>
                        <div><label class="form-label">Longitude</label><input type="text" id="addOfficeLng" name="longitude" class="form-control" placeholder="35.5018"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem">
                        <div><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                        <div><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm admin-plain-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Create Office</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade admin-modal office-location" id="editOfficeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Office</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editOfficeForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Municipality *</label>
                        <select name="municipality_id" id="editOfficeMuni" class="form-select" required>
                            @foreach(\App\Models\Municipality::all() as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" id="editOfficeName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Address *</label><input type="text" name="address" id="editOfficeAddress" class="form-control" required></div>
                    <div class="mb-3">
                        <label class="form-label">Location on Map</label>
                        <div id="editOfficeMap" class="admin-office-map">
                            <div class="admin-map-unavailable">Loading map...</div>
                        </div>
                        <div class="admin-map-help">
                            <i class="bi bi-pin-map"></i>
                            Click the map to move the office pin. Latitude and longitude will update automatically.
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:.75rem">
                        <div><label class="form-label">Latitude</label><input type="text" name="latitude" id="editOfficeLat" class="form-control" placeholder="33.8938"></div>
                        <div><label class="form-label">Longitude</label><input type="text" name="longitude" id="editOfficeLng" class="form-control" placeholder="35.5018"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-bottom:.75rem">
                        <div><label class="form-label">Phone</label><input type="text" name="phone" id="editOfficePhone" class="form-control"></div>
                        <div><label class="form-label">Email</label><input type="email" name="email" id="editOfficeEmail" class="form-control"></div>
                    </div>
                    <div><label class="form-label">Status</label>
                        <select name="is_active" id="editOfficeStatus" class="form-select">
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
const ADMIN_OFFICE_DEFAULT_LOCATION = { lat: 33.8938, lng: 35.5018 };
const adminOfficeMaps = {};
let pendingEditOfficeLocation = ADMIN_OFFICE_DEFAULT_LOCATION;

function toOfficeCoordinate(value) {
    const parsed = Number.parseFloat(value);
    return Number.isFinite(parsed) ? parsed : null;
}

function getOfficeLocationFromInputs(latInputId, lngInputId, fallback = ADMIN_OFFICE_DEFAULT_LOCATION) {
    const lat = toOfficeCoordinate(document.getElementById(latInputId)?.value);
    const lng = toOfficeCoordinate(document.getElementById(lngInputId)?.value);

    return lat !== null && lng !== null ? { lat, lng } : fallback;
}

function setOfficeLocationInputs(latInputId, lngInputId, location) {
    const latInput = document.getElementById(latInputId);
    const lngInput = document.getElementById(lngInputId);

    if (latInput) latInput.value = location.lat.toFixed(6);
    if (lngInput) lngInput.value = location.lng.toFixed(6);
}

function ensureOfficePickerMap(key, mapId, latInputId, lngInputId, fallbackLocation) {
    if (!window.google?.maps) {
        const mapEl = document.getElementById(mapId);
        if (mapEl) {
            mapEl.innerHTML = '<div class="admin-map-unavailable">Google Maps is not available. Enter coordinates manually.</div>';
        }
        return;
    }

    const mapEl = document.getElementById(mapId);
    if (!mapEl) return;

    const initialLocation = getOfficeLocationFromInputs(latInputId, lngInputId, fallbackLocation);
    setOfficeLocationInputs(latInputId, lngInputId, initialLocation);

    if (!adminOfficeMaps[key]) {
        const map = new google.maps.Map(mapEl, {
            center: initialLocation,
            zoom: 14,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
        });

        const marker = new google.maps.Marker({
            map,
            position: initialLocation,
            draggable: true,
        });

        const updateLocation = (location) => {
            marker.setPosition(location);
            map.panTo(location);
            setOfficeLocationInputs(latInputId, lngInputId, location);
        };

        map.addListener('click', (event) => {
            updateLocation({
                lat: event.latLng.lat(),
                lng: event.latLng.lng(),
            });
        });

        marker.addListener('dragend', (event) => {
            updateLocation({
                lat: event.latLng.lat(),
                lng: event.latLng.lng(),
            });
        });

        adminOfficeMaps[key] = { map, marker, updateLocation };
    } else {
        adminOfficeMaps[key].updateLocation(initialLocation);
    }

    setTimeout(() => {
        google.maps.event.trigger(adminOfficeMaps[key].map, 'resize');
        adminOfficeMaps[key].map.setCenter(adminOfficeMaps[key].marker.getPosition());
    }, 120);
}

window.initAdminOfficeMaps = function () {
    document.querySelectorAll('.modal.show .admin-office-map').forEach((mapEl) => {
        if (mapEl.id === 'addOfficeMap') {
            ensureOfficePickerMap('add', 'addOfficeMap', 'addOfficeLat', 'addOfficeLng', ADMIN_OFFICE_DEFAULT_LOCATION);
        }

        if (mapEl.id === 'editOfficeMap') {
            ensureOfficePickerMap('edit', 'editOfficeMap', 'editOfficeLat', 'editOfficeLng', pendingEditOfficeLocation);
        }
    });
};

document.querySelectorAll('.js-edit-office').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('editOfficeForm').action = `/admin/offices/${button.dataset.officeId}`;
        document.getElementById('editOfficeName').value = button.dataset.officeName || '';
        document.getElementById('editOfficeMuni').value = button.dataset.officeMunicipality || '';
        document.getElementById('editOfficeAddress').value = button.dataset.officeAddress || '';
        document.getElementById('editOfficeLat').value = button.dataset.officeLatitude || '';
        document.getElementById('editOfficeLng').value = button.dataset.officeLongitude || '';
        document.getElementById('editOfficePhone').value = button.dataset.officePhone || '';
        document.getElementById('editOfficeEmail').value = button.dataset.officeEmail || '';
        document.getElementById('editOfficeStatus').value = button.dataset.officeActive || '1';

        const lat = toOfficeCoordinate(button.dataset.officeLatitude);
        const lng = toOfficeCoordinate(button.dataset.officeLongitude);
        pendingEditOfficeLocation = lat !== null && lng !== null
            ? { lat, lng }
            : ADMIN_OFFICE_DEFAULT_LOCATION;

        bootstrap.Modal.getOrCreateInstance(document.getElementById('editOfficeModal')).show();
    });
});

document.getElementById('addOfficeModal')?.addEventListener('shown.bs.modal', () => {
    ensureOfficePickerMap('add', 'addOfficeMap', 'addOfficeLat', 'addOfficeLng', ADMIN_OFFICE_DEFAULT_LOCATION);
});

document.getElementById('editOfficeModal')?.addEventListener('shown.bs.modal', () => {
    ensureOfficePickerMap('edit', 'editOfficeMap', 'editOfficeLat', 'editOfficeLng', pendingEditOfficeLocation);
});

(function () {
    const url = new URL(window.location.href);
    if (url.searchParams.get('quick') !== 'add') return;
    const quickModalEl = document.getElementById('addOfficeModal');
    if (!quickModalEl || typeof bootstrap === 'undefined') return;

    bootstrap.Modal.getOrCreateInstance(quickModalEl).show();
    url.searchParams.delete('quick');
    const nextQuery = url.searchParams.toString();
    history.replaceState({}, '', `${url.pathname}${nextQuery ? `?${nextQuery}` : ''}${url.hash}`);
})();
</script>
@if(config('services.google_maps.api_key'))
<script
    async
    defer
    src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&callback=initAdminOfficeMaps">
</script>
@endif
@endpush
@endsection
