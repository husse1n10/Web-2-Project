@extends('layouts.app')
@section('title', 'Services')
@section('page-title', 'Services')

@section('content')
<div class="office-service-head office-reveal" data-office-reveal>
    <div>
        <h5 class="office-service-title">Manage Services</h5>
        <p class="office-service-sub">{{ $services->total() }} services</p>
    </div>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSvcModal">
        <i class="bi bi-plus-lg me-1"></i> Add Service
    </button>
</div>

<div class="card office-reveal" data-office-reveal>
    <div class="card-body p-0">
        <div class="d-none d-md-block table-responsive office-service-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $svc)
                        <tr>
                            <td>
                                <div class="office-service-name">{{ $svc->name }}</div>
                                @if($svc->description)
                                    <div class="office-service-desc">{{ Str::limit($svc->description, 58) }}</div>
                                @endif
                            </td>
                            <td class="office-service-category">{{ $svc->category->name ?? '-' }}</td>
                            <td class="office-service-price">${{ number_format($svc->price, 2) }}</td>
                            <td class="office-service-duration">{{ $svc->estimated_duration_days }} day(s)</td>
                            <td>
                                <x-status-pill :status="$svc->is_active ? 'approved' : 'rejected'" />
                            </td>
                            <td class="text-end">
                                <div class="office-service-actions">
                                    <button
                                        class="btn btn-sm office-icon-btn"
                                        onclick="editService({{ $svc->id }}, {{ Js::from($svc->name) }}, {{ $svc->price }}, {{ $svc->estimated_duration_days }}, {{ $svc->is_active ? 1 : 0 }}, {{ $svc->category_id ?? 'null' }})"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('office.services.destroy', $svc) }}" method="POST" onsubmit="return confirm('Delete this service?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm office-trash-btn"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="office-service-empty-cell">
                                <x-empty-state
                                    icon="bi-grid-3x3-gap"
                                    title="No services yet"
                                    message="Add your first office service to start receiving requests."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-md-none">
            @forelse($services as $svc)
                <div class="office-service-mobile-row">
                    <div class="office-service-mobile-icon">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                    <div class="office-service-mobile-main">
                        <div class="office-service-mobile-name">{{ $svc->name }}</div>
                        <div class="office-service-mobile-meta">${{ number_format($svc->price, 2) }} &middot; {{ $svc->estimated_duration_days }}d</div>
                    </div>
                    <x-status-pill :status="$svc->is_active ? 'approved' : 'rejected'" />
                </div>
            @empty
                <div class="office-service-empty-mobile">
                    <x-empty-state
                        icon="bi-grid-3x3-gap"
                        title="No services yet"
                        message="Create your first service."
                    />
                </div>
            @endforelse
        </div>

        @if($services->hasPages())
            <div class="office-service-pagination">{{ $services->links() }}</div>
        @endif
    </div>
</div>

<div class="modal fade" id="addSvcModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered office-modal-lg">
        <div class="modal-content office-modal-content">
            <div class="modal-header office-modal-header">
                <h6 class="modal-title">Add New Service</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('office.services.store') }}" method="POST">
                @csrf
                <div class="modal-body office-modal-body">
                    <div class="mb-3">
                        <label class="form-label">Service Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="office-modal-grid mb-3">
                        <div>
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div>
                            <label class="form-label">Currency</label>
                            <select name="currency" class="form-select">
                                <option>USD</option>
                                <option>LBP</option>
                                <option>EUR</option>
                            </select>
                        </div>
                    </div>
                    <div class="office-modal-grid mb-3">
                        <div>
                            <label class="form-label">Duration (days) *</label>
                            <input type="number" name="estimated_duration_days" class="form-control" min="1" value="1" required>
                        </div>
                        <div>
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">None</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Required Documents</label>
                        <div id="docsWrap">
                            <div class="office-doc-row">
                                <input type="text" name="required_documents[]" class="form-control form-control-sm" placeholder="e.g. National ID copy">
                                <button type="button" onclick="addDoc()" class="btn btn-sm office-doc-add-btn"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer office-modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editSvcModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered office-modal-md">
        <div class="modal-content office-modal-content">
            <div class="modal-header office-modal-header">
                <h6 class="modal-title">Edit Service</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSvcForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body office-modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" id="esName" class="form-control" required>
                    </div>
                    <div class="office-modal-grid mb-3">
                        <div>
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" id="esPrice" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div>
                            <label class="form-label">Duration (days)</label>
                            <input type="number" name="estimated_duration_days" id="esDuration" class="form-control" min="1">
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <select name="is_active" id="esActive" class="form-select">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer office-modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   OFFICE SERVICES — PREMIUM GLASSMORPHISM
   ═══════════════════════════════════════════════════════ */

body.es-role-office_user .office-service-head {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: .95rem; flex-wrap: wrap; gap: .75rem;
}
body.es-role-office_user .office-service-title {
    font-weight: 800; margin: 0; font-size: 1rem;
    background: linear-gradient(135deg, #1E3A8A, #2563EB);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
body.es-role-office_user .office-service-sub { color: #94A3B8; font-size: .78rem; margin: 0; }
body.es-role-office_user .office-service-name { font-weight: 600; font-size: .83rem; }
body.es-role-office_user .office-service-desc { font-size: .72rem; color: #94A3B8; }
body.es-role-office_user .office-service-category,
body.es-role-office_user .office-service-duration { color: #64748B; font-size: .8rem; }
body.es-role-office_user .office-service-price { font-weight: 700; }
body.es-role-office_user .office-service-actions { display: inline-flex; gap: .35rem; justify-content: flex-end; }

body.es-role-office_user .office-icon-btn {
    background: rgba(241,245,249,0.6); backdrop-filter: blur(4px);
    border: 1px solid rgba(37,99,235,0.08); color: #334155;
    transition: all .22s ease;
}
body.es-role-office_user .office-icon-btn:hover {
    background: linear-gradient(135deg, #2563EB, #0EA5E9); color: #fff; border-color: transparent;
}
body.es-role-office_user .office-trash-btn {
    background: rgba(254,226,226,0.6); backdrop-filter: blur(4px);
    border: 1px solid rgba(220,38,38,0.1); color: #DC2626;
    transition: all .22s ease;
}
body.es-role-office_user .office-trash-btn:hover {
    background: #DC2626; color: #fff; border-color: transparent;
}
body.es-role-office_user .office-service-empty-cell { padding: 1.8rem .8rem !important; }

/* Mobile rows — glass with hover */
body.es-role-office_user .office-service-mobile-row {
    padding: .9rem 1rem; border-bottom: 1px solid rgba(226,232,240,0.5);
    display: flex; align-items: center; gap: .75rem;
    transition: background .22s ease, transform .22s ease;
}
body.es-role-office_user .office-service-mobile-row:hover {
    background: rgba(224,242,254,0.15); transform: translateX(4px);
}
body.es-role-office_user .office-service-mobile-icon {
    width: 2.35rem; height: 2.35rem; border-radius: .7rem;
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    color: #fff; display: inline-flex; align-items: center; justify-content: center;
    font-size: .94rem; flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(37,99,235,0.18); border: none;
}
body.es-role-office_user .office-service-mobile-main { flex: 1; min-width: 0; }
body.es-role-office_user .office-service-mobile-name { font-weight: 700; font-size: .85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
body.es-role-office_user .office-service-mobile-meta { font-size: .73rem; color: #94A3B8; }
body.es-role-office_user .office-service-empty-mobile { padding: 1.25rem .55rem 1.4rem; }
body.es-role-office_user .office-service-pagination { padding: .75rem 1rem; border-top: 1px solid rgba(226,232,240,0.5); }

/* Modals — glass */
body.es-role-office_user .office-modal-lg { max-width: 540px; }
body.es-role-office_user .office-modal-md { max-width: 480px; }
body.es-role-office_user .office-modal-content {
    border: 1px solid rgba(37,99,235,0.08) !important;
    border-radius: .92rem;
    background: rgba(255,255,255,0.85) !important;
    backdrop-filter: blur(20px) saturate(1.6);
    -webkit-backdrop-filter: blur(20px) saturate(1.6);
    box-shadow: 0 24px 64px rgba(15,23,42,0.18);
}
body.es-role-office_user .office-modal-header { border: none; padding: 1.25rem 1.25rem .5rem; }
body.es-role-office_user .office-modal-header .modal-title { font-weight: 800; }
body.es-role-office_user .office-modal-body { padding: .75rem 1.25rem; }
body.es-role-office_user .office-modal-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .65rem; }
body.es-role-office_user .office-doc-row { display: flex; gap: .4rem; margin-bottom: .4rem; }
body.es-role-office_user .office-doc-add-btn {
    background: linear-gradient(135deg, #2563EB, #0EA5E9);
    border: none; color: #fff; flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(37,99,235,0.2);
}
body.es-role-office_user .office-doc-remove-btn {
    background: #FEE2E2; border: none; color: #DC2626; flex-shrink: 0;
}
body.es-role-office_user .office-modal-footer { border: none; padding: .75rem 1.25rem 1.25rem; gap: .5rem; }

@media (max-width: 575.98px) {
    body.es-role-office_user .office-modal-grid { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) {
    body.es-role-office_user .office-icon-btn,
    body.es-role-office_user .office-trash-btn,
    body.es-role-office_user .office-service-mobile-row { transition: none; }
}
</style>
@endpush

@push('scripts')
<script>
function addDoc() {
    const wrap = document.getElementById('docsWrap');
    const row = document.createElement('div');
    row.className = 'office-doc-row';
    row.innerHTML = `
        <input type="text" name="required_documents[]" class="form-control form-control-sm" placeholder="Document name">
        <button type="button" onclick="this.parentElement.remove()" class="btn btn-sm office-doc-remove-btn"><i class="bi bi-dash"></i></button>
    `;
    wrap.appendChild(row);
}

function editService(id, name, price, duration, active) {
    document.getElementById('editSvcForm').action = `/office/services/${id}`;
    document.getElementById('esName').value = name;
    document.getElementById('esPrice').value = price;
    document.getElementById('esDuration').value = duration;
    document.getElementById('esActive').value = active;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('editSvcModal')).show();
}
</script>
@endpush
