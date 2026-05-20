@extends('layouts.app')
@section('title','Reports')
@section('page-title','Reports & Analytics')

@push('styles')
<style>
    /* ═══ ADMIN REPORTS — PREMIUM GLASS ═══ */
    .reports-grid { display: grid; grid-template-columns: 1fr; gap: 1rem; }
    .report-reveal { transition: opacity .36s ease, transform .36s ease; }
    body.es-role-admin.reports-motion .report-reveal { opacity: 0; transform: translateY(10px); }
    body.es-role-admin.reports-motion .report-reveal.is-visible { opacity: 1; transform: translateY(0); }

    .report-row { display: flex; align-items: center; gap: .75rem; margin-bottom: .75rem; }
    .report-label { font-size: .78rem; font-weight: 500; color: #566A7F; min-width: 110px; flex-shrink: 0; }

    /* Status bar track — glass */
    .report-track {
        flex: 1; height: 22px;
        background: rgba(255,255,255,0.4);
        backdrop-filter: blur(4px);
        border-radius: 99px; overflow: hidden;
        border: 1px solid rgba(79,70,229,0.06);
    }
    .report-fill {
        height: 100%; border-radius: 99px;
        transition: width .55s cubic-bezier(.2,.8,.2,1);
        box-shadow: 0 2px 8px rgba(79,70,229,0.15);
    }
    .report-count { font-size: .78rem; font-weight: 700; color: #566A7F; min-width: 30px; text-align: right; }

    /* Chips — gradient */
    .report-chip {
        background: linear-gradient(135deg, #4F46E5, #2563EB);
        color: #fff; border: none;
        padding: .2rem .6rem; border-radius: 20px;
        font-size: .72rem; font-weight: 600;
        box-shadow: 0 2px 6px rgba(79,70,229,0.2);
    }
    .report-empty { text-align: center; padding: 1.5rem; color: var(--es-muted); }

    /* Month chart — enhanced bars */
    .report-month-chart { display: flex; align-items: flex-end; gap: .4rem; height: 120px; padding-bottom: .5rem; }
    .report-month-col { flex: 1; display: flex; flex-direction: column; align-items: center; gap: .3rem; height: 100%; }
    .report-month-track { flex: 1; width: 100%; display: flex; align-items: flex-end; }
    .report-month-bar {
        width: 100%; border-radius: 4px 4px 0 0;
        transition: transform .55s ease, opacity .4s ease;
        box-shadow: 0 -2px 8px rgba(79,70,229,0.1);
    }
    body.es-role-admin.reports-motion .report-month-bar { transform-origin: bottom; transform: scaleY(.4); opacity: .68; }
    body.es-role-admin.reports-motion .report-month-chart.is-ready .report-month-bar { transform: scaleY(1); opacity: 1; }
    .report-month-label { font-size: .6rem; color: var(--es-muted); font-weight: 500; }

    @media(min-width: 768px) {
        .reports-grid { grid-template-columns: 1fr 1fr; }
        .reports-grid .card:last-child { grid-column: 1 / -1; }
    }
    @media (prefers-reduced-motion: reduce) {
        .report-reveal, .report-fill, .report-month-bar { transition: none !important; }
    }
</style>
@endpush

@section('content')

@php
    $totalRevenue  = $revenueByOffice->sum('revenue') ?? 0;
    $totalRequests = $requestsByStatus->sum();
    $completed     = $requestsByStatus->get('completed', 0);
    $rate          = $totalRequests > 0 ? round(($completed / $totalRequests) * 100) : 0;
@endphp

<x-admin.page-header
    title="Reports & Analytics"
    subtitle="Live operational metrics and request trends across offices." />

<div class="d-flex flex-wrap gap-2 mb-3 admin-reveal">
    <a href="{{ route('admin.reports.export.pdf') }}" class="btn btn-sm btn-primary">
        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
    </a>
    <a href="{{ route('admin.reports.export', 'requests') }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-download me-1"></i> Export Requests CSV
    </a>
    <a href="{{ route('admin.reports.export', 'payments') }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-download me-1"></i> Export Payments CSV
    </a>
    <a href="{{ route('admin.reports.export', 'offices') }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-download me-1"></i> Export Offices CSV
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3 admin-reveal">
        <x-admin.stat-card
            label="Total Requests"
            :value="number_format($totalRequests)"
            subtitle="Across all statuses"
            icon="bi-file-earmark-check"
            color="sky" />
    </div>
    <div class="col-6 col-xl-3 admin-reveal">
        <x-admin.stat-card
            label="Total Revenue"
            :value="'$' . number_format($totalRevenue, 0)"
            subtitle="Collected from paid requests"
            icon="bi-cash-coin"
            color="emerald" />
    </div>
    <div class="col-6 col-xl-3 admin-reveal">
        <x-admin.stat-card
            label="Completion Rate"
            :value="$rate . '%'"
            subtitle="Approved and completed"
            icon="bi-check-circle"
            color="amber" />
    </div>
    <div class="col-6 col-xl-3 admin-reveal">
        <x-admin.stat-card
            label="Pending Now"
            :value="number_format($requestsByStatus->get('pending', 0))"
            subtitle="Awaiting review"
            icon="bi-hourglass-split"
            color="violet" />
    </div>
</div>

<div class="reports-grid">
    <div class="card report-reveal admin-reveal js-status-card">
        <x-admin.table-toolbar
            title="Requests by Status"
            subtitle="Distribution of current workflow stages." />
        <div class="card-body pt-3">
            @php
                $statusLabels = [
                    'pending'            => 'Pending',
                    'in_review'          => 'In Review',
                    'missing_documents'  => 'Missing Docs',
                    'approved'           => 'Approved',
                    'rejected'           => 'Rejected',
                    'completed'          => 'Completed',
                ];
                $statusColors = [
                    'pending'            => 'var(--es-amber)',
                    'in_review'          => 'var(--es-primary)',
                    'missing_documents'  => 'var(--es-rose)',
                    'approved'           => 'var(--es-emerald)',
                    'rejected'           => 'var(--es-rose)',
                    'completed'          => '#10B981',
                ];
                $maxCount = max(1, $requestsByStatus->max() ?? 1);
            @endphp
            @foreach($statusLabels as $key => $label)
            @php
                $count = $requestsByStatus->get($key, 0);
                $width = $maxCount > 0 ? round(($count / $maxCount) * 100) : 0;
            @endphp
            <div class="report-row">
                <div class="report-label">{{ $label }}</div>
                <div class="report-track">
                    <div class="report-fill" data-width="{{ $width }}" style="background:{{ $statusColors[$key] }};width:{{ $width }}%;"></div>
                </div>
                <div class="report-count">{{ $count }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card report-reveal admin-reveal admin-busy-target" id="adminReportsOfficeTableCard">
        <x-admin.table-toolbar
            title="Requests per Office"
            subtitle="Request volume and revenue by office.">
            <x-slot:actions>
                <div class="admin-density-switch">
                    <button
                        type="button"
                        class="admin-density-btn is-active"
                        data-admin-density-target="#adminReportsOfficeTable"
                        data-admin-density="comfortable">Comfort</button>
                    <button
                        type="button"
                        class="admin-density-btn"
                        data-admin-density-target="#adminReportsOfficeTable"
                        data-admin-density="compact">Compact</button>
                </div>
            </x-slot:actions>
        </x-admin.table-toolbar>
        <div class="card-body p-0">
            <div class="admin-table-wrap">
                <table id="adminReportsOfficeTable" class="table table-hover admin-table-sticky admin-table-interactive" data-admin-table>
                    <thead>
                        <tr>
                            <th data-sort="0" data-sort-type="text">Office</th>
                            <th data-sort="1" data-sort-type="text">Municipality</th>
                            <th data-sort="2" data-sort-type="number">Requests</th>
                            <th data-sort="3" data-sort-type="number">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requestsByOffice as $office)
                        <tr>
                            <td style="font-weight:600;font-size:.82rem">{{ $office->name }}</td>
                            <td style="font-size:.78rem;color:var(--es-muted)">{{ optional($office->municipality)->name ?? '-' }}</td>
                            <td>
                                <span class="report-chip">{{ $office->requests_count }}</span>
                            </td>
                            <td style="font-weight:600;font-size:.82rem">${{ number_format($revenueByOffice->firstWhere('id', $office->id)?->revenue ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="report-empty">No data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card report-reveal admin-reveal js-month-card">
        <x-admin.table-toolbar
            title="Monthly Requests - {{ date('Y') }}"
            subtitle="Current month is highlighted for quick scanning." />
        <div class="card-body">
            @php
                $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                $maxMonth = max(1, $monthlyRequests->max() ?? 1);
            @endphp
            <div class="report-month-chart" id="reportMonthChart">
                @foreach($months as $i => $m)
                @php $count = $monthlyRequests->get($i + 1, 0); $h = max(4, round(($count / $maxMonth) * 100)); @endphp
                <div class="report-month-col">
                    <div class="report-month-track">
                        <div
                            class="report-month-bar"
                            style="height:{{ $h }}%;background:{{ ($i + 1) == now()->month ? 'var(--es-primary)' : 'var(--es-primary-m)' }};"
                            title="{{ $m }}: {{ $count }}"></div>
                    </div>
                    <div class="report-month-label">{{ $m }}</div>
                </div>
                @endforeach
            </div>
            <div style="font-size:.7rem;color:var(--es-muted);text-align:center;margin-top:.25rem">
                <span style="display:inline-flex;align-items:center;gap:.25rem">
                    <span style="width:10px;height:10px;border-radius:2px;background:var(--es-primary);display:inline-block"></span> Current month
                    <span style="width:10px;height:10px;border-radius:2px;background:var(--es-primary-m);display:inline-block;margin-left:.5rem"></span> Other months
                </span>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    (function () {
        const revealCards = Array.from(document.querySelectorAll('.report-reveal'));
        const statusCard = document.querySelector('.js-status-card');
        const monthCard = document.querySelector('.js-month-card');
        const monthChart = document.getElementById('reportMonthChart');
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const animateStatusBars = () => {
            const bars = Array.from(document.querySelectorAll('.report-fill[data-width]'));
            if (prefersReducedMotion) {
                bars.forEach((bar) => {
                    bar.style.width = `${Number(bar.dataset.width || 0)}%`;
                });
                return;
            }

            bars.forEach((bar, index) => {
                const width = Number(bar.dataset.width || 0);
                bar.style.width = '0%';
                window.setTimeout(() => {
                    bar.style.width = `${width}%`;
                }, 55 * index);
            });
        };

        const revealAllNow = () => {
            revealCards.forEach((card, index) => {
                window.setTimeout(() => {
                    card.classList.add('is-visible');
                }, prefersReducedMotion ? 0 : index * 70);
            });
            animateStatusBars();
            monthChart?.classList.add('is-ready');
        };

        if (!prefersReducedMotion) {
            document.body.classList.add('reports-motion');
        }

        if (!('IntersectionObserver' in window)) {
            revealAllNow();
            return;
        }

        const cardObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12 });

        revealCards.forEach((card) => cardObserver.observe(card));

        if (statusCard) {
            const statusObserver = new IntersectionObserver((entries, observer) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    animateStatusBars();
                    observer.disconnect();
                }
            }, { threshold: 0.2 });
            statusObserver.observe(statusCard);
        }

        if (monthCard && monthChart) {
            const monthObserver = new IntersectionObserver((entries, observer) => {
                if (entries.some((entry) => entry.isIntersecting)) {
                    monthChart.classList.add('is-ready');
                    observer.disconnect();
                }
            }, { threshold: 0.2 });
            monthObserver.observe(monthCard);
        }
    })();
</script>
@endpush
