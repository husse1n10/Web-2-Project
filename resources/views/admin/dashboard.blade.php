@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════════
   ADMIN DASHBOARD — PREMIUM GLASSMORPHISM STYLES
   ═══════════════════════════════════════════════════════ */

/* Filter bar — glass */
.admin-dashboard-filters {
    border: 1px solid rgba(79,70,229,0.08) !important;
    border-radius: .85rem;
    background: rgba(255,255,255,0.5) !important;
    backdrop-filter: blur(14px) saturate(1.5);
    -webkit-backdrop-filter: blur(14px) saturate(1.5);
    margin-bottom: 1rem;
}
.admin-dashboard-filters .card-body { padding: .9rem 1rem; }
.admin-filter-grid {
    display: grid;
    grid-template-columns: 1.25fr 1fr 1fr auto;
    gap: .65rem;
    align-items: end;
}
.admin-filter-actions { display: flex; gap: .5rem; align-items: center; }
.admin-filter-label {
    font-size: .72rem;
    color: var(--es-muted);
    font-weight: 600;
    margin-bottom: .26rem;
}
.admin-filter-badge {
    display: inline-flex;
    align-items: center;
    gap: .24rem;
    background: linear-gradient(135deg, #4F46E5, #2563EB);
    color: #fff;
    border: none;
    border-radius: 999px;
    padding: .2rem .56rem;
    font-size: .64rem;
    font-weight: 700;
    margin-left: .45rem;
    box-shadow: 0 2px 8px rgba(79,70,229,0.22);
}

/* KPI cards — glass with unique gradient accent bars */
.admin-kpi-card {
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(79,70,229,0.08) !important;
    background: rgba(255,255,255,0.5) !important;
    backdrop-filter: blur(12px) saturate(1.4);
    -webkit-backdrop-filter: blur(12px) saturate(1.4);
    transition: transform .28s cubic-bezier(.4,0,.2,1), box-shadow .28s cubic-bezier(.4,0,.2,1);
}
.admin-kpi-card::before {
    content: '';
    position: absolute;
    inset: 0 auto auto 0;
    width: 100%;
    height: 3px;
    opacity: .5;
    transition: opacity .28s ease;
}
.admin-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 16px 32px rgba(79,70,229,0.1);
}
.admin-kpi-card:hover::before { opacity: 1; }

.col-6:nth-child(1) .admin-kpi-card::before { background: linear-gradient(90deg, #14B8A6, #10B981); }
.col-6:nth-child(2) .admin-kpi-card::before { background: linear-gradient(90deg, #0EA5E9, #2563EB); }
.col-6:nth-child(3) .admin-kpi-card::before { background: linear-gradient(90deg, #F59E0B, #EF4444); }
.col-6:nth-child(4) .admin-kpi-card::before { background: linear-gradient(90deg, #10B981, #059669); }

/* Office ranking list — glass items */
.admin-list-item {
    border: 1px solid rgba(79,70,229,0.06) !important;
    background: rgba(255,255,255,0.4) !important;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    transition: transform .22s cubic-bezier(.4,0,.2,1), box-shadow .22s ease, border-color .22s ease;
}
.admin-list-item:hover {
    border-color: rgba(79,70,229,0.15) !important;
    background: rgba(255,255,255,0.6) !important;
    transform: translateX(4px);
    box-shadow: 0 8px 20px rgba(79,70,229,0.08);
}
.admin-list-item-main {
    font-weight: 700;
    font-size: .84rem;
    color: #1F2A3D;
}
.admin-list-item-sub {
    color: var(--es-muted);
    font-size: .72rem;
    margin-top: .12rem;
}
.admin-count-badge {
    background: linear-gradient(135deg, #4F46E5, #2563EB) !important;
    color: #fff !important;
    border: none !important;
    min-width: 28px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(79,70,229,0.2);
}

/* Chart — glass wrapper */
.admin-chart-wrap {
    position: relative;
    min-height: 250px;
}
.admin-chart-wrap canvas { height: 250px !important; }

.admin-table-tight td,
.admin-table-tight th {
    padding-top: .62rem;
    padding-bottom: .62rem;
}
.admin-card-meta {
    font-size: .72rem;
    color: var(--es-muted);
    font-weight: 500;
}
.admin-empty-state .btn { margin-top: .75rem; }

/* Live loading overlay — glass blur */
.admin-dashboard-live {
    position: relative;
    transition: opacity .22s ease;
}
.admin-dashboard-live.is-loading {
    opacity: .58;
    pointer-events: none;
}
.admin-dashboard-live.is-loading::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 19;
    background: rgba(255,255,255,.3);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}
.admin-dashboard-live.is-loading::after {
    content: '';
    position: absolute;
    top: 1rem; left: 1rem;
    width: min(18rem, calc(100% - 2rem));
    height: .82rem;
    border-radius: .45rem;
    background: linear-gradient(90deg, rgba(79,70,229,.15) 0%, rgba(79,70,229,.35) 50%, rgba(79,70,229,.15) 100%);
    z-index: 20;
    animation: adminBusyShimmer 1.05s ease-in-out infinite;
}

@media (max-width: 1199.98px) {
    .admin-filter-grid { grid-template-columns: 1fr 1fr; }
    .admin-filter-actions { grid-column: 1 / -1; justify-content: flex-start; }
}
@media (max-width: 767.98px) {
    .admin-filter-grid { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) {
    .admin-kpi-card,
    .admin-list-item { transition: none; }
}
</style>
@endpush

@section('content')
<div id="adminDashboardLiveRoot" class="admin-dashboard-live admin-busy-target">
<x-admin.page-header
    title="Admin Overview"
    :subtitle="'Live operational summary for ' . strtolower($dashboardFilters['period_label']) . '.'">
    <x-slot:actions>
        @if($dashboardFilters['is_scoped'])
            <span class="admin-filter-badge"><i class="bi bi-funnel"></i> Filtered</span>
        @endif
    </x-slot:actions>
</x-admin.page-header>

<div class="card admin-dashboard-filters admin-reveal">
    <div class="card-body">
        <form
            id="adminDashboardFilters"
            method="GET"
            action="{{ route('admin.dashboard') }}"
            class="admin-filter-grid"
            data-admin-busy-target="#adminDashboardLiveRoot">
            <div>
                <label class="admin-filter-label" for="dashboardPeriod">Date Range</label>
                <select id="dashboardPeriod" name="period" class="form-select form-select-sm">
                    <option value="this_month" {{ $dashboardFilters['period'] === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_30" {{ $dashboardFilters['period'] === 'last_30' ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="last_90" {{ $dashboardFilters['period'] === 'last_90' ? 'selected' : '' }}>Last 90 Days</option>
                    <option value="this_year" {{ $dashboardFilters['period'] === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="all" {{ $dashboardFilters['period'] === 'all' ? 'selected' : '' }}>All Time</option>
                </select>
            </div>
            <div>
                <label class="admin-filter-label" for="dashboardMunicipality">Municipality</label>
                <select id="dashboardMunicipality" name="municipality_id" class="form-select form-select-sm">
                    <option value="">All Municipalities</option>
                    @foreach($municipalities as $municipality)
                        <option value="{{ $municipality->id }}" {{ (int) $dashboardFilters['municipality_id'] === $municipality->id ? 'selected' : '' }}>
                            {{ $municipality->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="admin-filter-label" for="dashboardOffice">Office</label>
                <select id="dashboardOffice" name="office_id" class="form-select form-select-sm">
                    <option value="">All Offices</option>
                    @foreach($officeOptions as $officeOption)
                        <option value="{{ $officeOption->id }}" {{ (int) $dashboardFilters['office_id'] === $officeOption->id ? 'selected' : '' }}>
                            {{ $officeOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-actions">
                <button id="adminDashboardApply" type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-check2"></i> Apply
                </button>
                @if($dashboardFilters['is_scoped'])
                    <a id="adminDashboardClear" href="{{ route('admin.dashboard') }}" class="btn btn-sm admin-plain-btn">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3 admin-reveal">
        <x-admin.stat-card
            label="Total Users"
            :value="number_format($stats['total_users'])"
            subtitle="Platform citizens"
            icon="bi-people"
            color="teal"
            :trend="$trends['total_users']['text']"
            :trend-direction="$trends['total_users']['direction']"
            :animate="true"
            :value-raw="$stats['total_users']"
            value-decimals="0" />
    </div>
    <div class="col-6 col-lg-3 admin-reveal">
        <x-admin.stat-card
            label="Offices"
            :value="number_format($stats['total_offices'])"
            subtitle="Offices in scope"
            icon="bi-buildings"
            color="sky"
            :trend="$trends['total_offices']['text']"
            :trend-direction="$trends['total_offices']['direction']"
            :animate="true"
            :value-raw="$stats['total_offices']"
            value-decimals="0" />
    </div>
    <div class="col-6 col-lg-3 admin-reveal">
        <x-admin.stat-card
            label="Requests"
            :value="number_format($stats['total_requests'])"
            :subtitle="'During ' . strtolower($dashboardFilters['period_label'])"
            icon="bi-file-earmark-text"
            color="amber"
            :trend="$trends['total_requests']['text']"
            :trend-direction="$trends['total_requests']['direction']"
            :animate="true"
            :value-raw="$stats['total_requests']"
            value-decimals="0" />
    </div>
    <div class="col-6 col-lg-3 admin-reveal">
        <x-admin.stat-card
            label="Revenue"
            :value="$stats['total_revenue_breakdown']"
            :subtitle="$stats['total_revenue_currency_count'] > 1 ? 'Paid request totals by currency' : 'Collected from paid requests'"
            icon="bi-currency-dollar"
            color="emerald"
            :trend="$trends['total_revenue']['text']"
            :trend-direction="$trends['total_revenue']['direction']"
            :trend-label="$stats['total_revenue_currency_count'] > 1 ? 'mixed currencies' : 'vs previous period'"
        />
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-xl-8 admin-reveal">
        <div class="card h-100 admin-busy-target" id="adminDashboardRequestsCard">
            <x-admin.table-toolbar
                title="Recent Service Requests"
                subtitle="Latest request activity across municipalities and offices.">
                <x-slot:actions>
                    <span class="admin-card-meta">Pending: {{ number_format($stats['pending_requests']) }}</span>
                    <div class="admin-density-switch ms-2">
                        <button
                            type="button"
                            class="admin-density-btn is-active"
                            data-admin-density-target="#adminDashboardRequestsTable"
                            data-admin-density="comfortable">Comfort</button>
                        <button
                            type="button"
                            class="admin-density-btn"
                            data-admin-density-target="#adminDashboardRequestsTable"
                            data-admin-density="compact">Compact</button>
                    </div>
                </x-slot:actions>
            </x-admin.table-toolbar>
            <div class="card-body p-0">
                @if($recentRequests->count())
                    <div class="admin-table-wrap">
                        <table
                            id="adminDashboardRequestsTable"
                            class="table table-hover admin-table-sticky admin-table-tight admin-table-interactive mb-0"
                            data-admin-table>
                            <thead>
                                <tr>
                                    <th data-sort="0" data-sort-type="text">Reference</th>
                                    <th data-sort="1" data-sort-type="text">Citizen</th>
                                    <th data-sort="2" data-sort-type="text">Office</th>
                                    <th data-sort="3" data-sort-type="text">Service</th>
                                    <th data-sort="4" data-sort-type="text">Status</th>
                                    <th data-sort="5" data-sort-type="date">Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRequests as $request)
                                    <tr>
                                        <td><span class="fw-semibold">{{ $request->reference_number }}</span></td>
                                        <td>{{ $request->citizen->name }}</td>
                                        <td>{{ $request->office->name }}</td>
                                        <td>{{ $request->resolved_service_name }}</td>
                                        <td><x-status-pill :status="$request->status" /></td>
                                        <td class="admin-muted" data-sort-value="{{ $request->created_at->timestamp }}">{{ $request->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="admin-empty-state">
                        <div class="admin-empty-state-icon"><i class="bi bi-inbox"></i></div>
                        <div class="admin-empty-state-title">No Requests In This Range</div>
                        <div class="admin-empty-state-copy">Try widening the date range or clearing office filters to see more activity.</div>
                        @if($dashboardFilters['is_scoped'])
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-sm admin-plain-btn">Reset Filters</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4 admin-reveal">
        <div class="card h-100">
            <x-admin.table-toolbar
                title="Top Offices by Volume"
                subtitle="Highest request counts in the selected scope." />
            <div class="card-body">
                @if($officeStats->count())
                    <div class="d-flex flex-column gap-2">
                        @foreach($officeStats as $office)
                            <div class="d-flex justify-content-between align-items-center border rounded-3 p-3 admin-list-item">
                                <div>
                                    <div class="admin-list-item-main">{{ $office->name }}</div>
                                    <div class="admin-list-item-sub">{{ $office->municipality->name ?? 'Municipality' }}</div>
                                </div>
                                <span class="badge rounded-pill border admin-count-badge">{{ $office->requests_count }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="admin-empty-state" style="padding-top:1.5rem;padding-bottom:1.5rem;">
                        <div class="admin-empty-state-icon"><i class="bi bi-bar-chart"></i></div>
                        <div class="admin-empty-state-title">No Office Ranking Yet</div>
                        <div class="admin-empty-state-copy">Office activity appears once requests are submitted in the selected range.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 admin-reveal">
        <div class="card">
            <x-admin.table-toolbar
                title="Monthly Requests"
                :subtitle="now()->year . ' trend for ' . strtolower($dashboardFilters['period_label']) . '.'" />
            <div class="card-body">
                @if(collect($chartValues)->sum() > 0)
                    <div class="admin-chart-wrap">
                        <canvas
                            id="monthlyRequestsChart"
                            aria-label="Monthly requests chart"
                            data-chart-labels='@json($chartLabels)'
                            data-chart-values='@json($chartValues)'></canvas>
                    </div>
                @else
                    <div class="admin-empty-state" style="padding-top:1.25rem;padding-bottom:1.25rem;">
                        <div class="admin-empty-state-icon"><i class="bi bi-graph-up"></i></div>
                        <div class="admin-empty-state-title">No Trend Data Yet</div>
                        <div class="admin-empty-state-copy">No requests matched your current scope for chart visualization.</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let autoSubmitTimer = null;

        const formatNumber = (value, decimals) =>
            Number(value).toLocaleString(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });

        const setApplyLoading = (form, loading) => {
            if (!form) return;
            const applyBtn = form.querySelector('#adminDashboardApply');
            if (!applyBtn) return;
            if (!applyBtn.dataset.originalHtml) {
                applyBtn.dataset.originalHtml = applyBtn.innerHTML;
            }
            applyBtn.disabled = loading;
            applyBtn.innerHTML = loading
                ? '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Applying'
                : applyBtn.dataset.originalHtml;
        };

        const animateCounters = (scope) => {
            scope.querySelectorAll('[data-counter]').forEach((el, index) => {
                const target = Number(el.dataset.counterTarget || 0);
                const prefix = el.dataset.counterPrefix || '';
                const suffix = el.dataset.counterSuffix || '';
                const decimals = Number(el.dataset.counterDecimals || 0);

                const setValue = (value) => {
                    el.textContent = `${prefix}${formatNumber(value, decimals)}${suffix}`;
                };

                if (!Number.isFinite(target) || prefersReducedMotion) {
                    setValue(target);
                    return;
                }

                const duration = 850 + (index * 120);
                const startTime = performance.now();

                const tick = (now) => {
                    const progress = Math.min((now - startTime) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    setValue(target * eased);
                    if (progress < 1) {
                        window.requestAnimationFrame(tick);
                    }
                };

                window.requestAnimationFrame(tick);
            });
        };

        const renderMonthlyChart = (scope) => {
            const canvas = scope.querySelector('#monthlyRequestsChart');
            if (window.__adminMonthlyChart) {
                window.__adminMonthlyChart.destroy();
                window.__adminMonthlyChart = null;
            }
            if (!canvas || typeof Chart === 'undefined') return;

            const labels = JSON.parse(canvas.dataset.chartLabels || '[]');
            const values = JSON.parse(canvas.dataset.chartValues || '[]');

            const css = getComputedStyle(document.body);
            const primary = css.getPropertyValue('--es-primary').trim() || '#2563EB';
            const muted = css.getPropertyValue('--es-muted').trim() || '#64748B';
            const border = css.getPropertyValue('--es-border-soft').trim() || '#E7EDF5';

            window.__adminMonthlyChart = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Requests',
                        data: values,
                        borderColor: primary,
                        backgroundColor: `${primary}1f`,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: primary,
                        pointHoverRadius: 5,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: muted, font: { size: 11, family: 'Public Sans' } },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, color: muted, font: { size: 11, family: 'Public Sans' } },
                            grid: { color: border },
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0F172A',
                            titleFont: { family: 'Public Sans' },
                            bodyFont: { family: 'Public Sans' }
                        }
                    }
                }
            });
        };

        const buildFilterUrl = (form) => {
            const url = new URL(form.action, window.location.origin);
            const params = new URLSearchParams();
            const formData = new FormData(form);
            for (const [key, value] of formData.entries()) {
                if (String(value).trim() === '') continue;
                params.set(key, value);
            }
            if (!params.has('period')) {
                params.set('period', 'this_month');
            }
            url.search = params.toString();
            return url;
        };

        const swapDashboard = async (url, sourceForm) => {
            const currentRoot = document.getElementById('adminDashboardLiveRoot');
            if (!currentRoot) return;

            currentRoot.classList.add('is-loading');
            setApplyLoading(sourceForm, true);

            try {
                const response = await fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error(`Dashboard request failed (${response.status})`);
                const html = await response.text();
                const parser = new DOMParser();
                const nextDoc = parser.parseFromString(html, 'text/html');
                const nextRoot = nextDoc.getElementById('adminDashboardLiveRoot');
                if (!nextRoot) throw new Error('Live root not found in response');

                currentRoot.replaceWith(nextRoot);
                history.replaceState({}, '', url.toString());
                initDashboardEnhancements();
            } catch (error) {
                window.location.href = url.toString();
            } finally {
                setApplyLoading(sourceForm, false);
                currentRoot.classList.remove('is-loading');
            }
        };

        const bindDashboardFilters = () => {
            const form = document.getElementById('adminDashboardFilters');
            if (!form || form.dataset.ajaxBound === '1') return;

            form.dataset.ajaxBound = '1';
            const municipalitySelect = form.querySelector('#dashboardMunicipality');
            const officeSelect = form.querySelector('#dashboardOffice');
            const periodSelect = form.querySelector('#dashboardPeriod');
            const clearLink = form.querySelector('#adminDashboardClear');

            const requestLiveUpdate = () => {
                if (form.dataset.loading === '1') return;
                const url = buildFilterUrl(form);
                form.dataset.loading = '1';
                swapDashboard(url, form).finally(() => {
                    form.dataset.loading = '0';
                });
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                requestLiveUpdate();
            });

            periodSelect?.addEventListener('change', () => {
                window.clearTimeout(autoSubmitTimer);
                autoSubmitTimer = window.setTimeout(() => form.requestSubmit(), 120);
            });

            municipalitySelect?.addEventListener('change', () => {
                if (officeSelect) officeSelect.value = '';
                window.clearTimeout(autoSubmitTimer);
                autoSubmitTimer = window.setTimeout(() => form.requestSubmit(), 120);
            });

            officeSelect?.addEventListener('change', () => {
                window.clearTimeout(autoSubmitTimer);
                autoSubmitTimer = window.setTimeout(() => form.requestSubmit(), 120);
            });

            clearLink?.addEventListener('click', (event) => {
                event.preventDefault();
                form.reset();
                const clearUrl = new URL(clearLink.href, window.location.origin);
                form.dataset.loading = '1';
                swapDashboard(clearUrl, form).finally(() => {
                    form.dataset.loading = '0';
                });
            });
        };

        const initDashboardEnhancements = () => {
            const root = document.getElementById('adminDashboardLiveRoot');
            if (!root) return;
            animateCounters(root);
            renderMonthlyChart(root);
            bindDashboardFilters();
            if (typeof window.initAdminGlobalUX === 'function') {
                window.initAdminGlobalUX(root);
            }
        };

        initDashboardEnhancements();
    })();
</script>
@endpush
