<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reports & Analytics</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }
        .meta {
            color: #64748b;
            margin-top: 4px;
        }
        .section {
            margin-top: 16px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #1e293b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #eff6ff;
            color: #334155;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
            text-align: left;
            padding: 8px;
            border: 1px solid #dbeafe;
        }
        td {
            padding: 8px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .metric-table td {
            width: 25%;
            border-color: #dbeafe;
        }
        .metric-label {
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 4px;
        }
        .metric-value {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }
        .muted { color: #64748b; }
        .text-right { text-align: right; }
        .status-count {
            font-weight: 700;
            color: #0f172a;
        }
        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 10px;
        }
    </style>
</head>
<body>
@php
    $statusLabels = [
        'pending' => 'Pending',
        'in_review' => 'In Review',
        'missing_documents' => 'Missing Docs',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
    ];
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
@endphp

<div class="header">
    <div class="brand">CedarGov Reports & Analytics</div>
    <div class="meta">
        Generated {{ $generatedAt->format('M d, Y H:i') }}
        @if($generatedBy)
            by {{ $generatedBy->name }}
        @endif
    </div>
</div>

<table class="metric-table">
    <tr>
        <td>
            <div class="metric-label">Total Requests</div>
            <div class="metric-value">{{ number_format($totalRequests) }}</div>
            <div class="muted">Across all statuses</div>
        </td>
        <td>
            <div class="metric-label">Total Revenue</div>
            <div class="metric-value">${{ number_format((float) $totalRevenue, 0) }}</div>
            <div class="muted">Collected from paid requests</div>
        </td>
        <td>
            <div class="metric-label">Completion Rate</div>
            <div class="metric-value">{{ $completionRate }}%</div>
            <div class="muted">Completed requests</div>
        </td>
        <td>
            <div class="metric-label">Pending Now</div>
            <div class="metric-value">{{ number_format($pendingNow) }}</div>
            <div class="muted">Awaiting review</div>
        </td>
    </tr>
</table>

<div class="section">
    <div class="section-title">Requests by Status</div>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="text-right">Requests</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statusLabels as $status => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="text-right status-count">{{ number_format($requestsByStatus->get($status, 0)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section-title">Requests per Office</div>
    <table>
        <thead>
            <tr>
                <th>Office</th>
                <th>Municipality</th>
                <th class="text-right">Requests</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requestsByOffice as $office)
                <tr>
                    <td>{{ $office->name }}</td>
                    <td>{{ optional($office->municipality)->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($office->requests_count) }}</td>
                    <td class="text-right">${{ number_format((float) ($revenueByOffice->firstWhere('id', $office->id)?->revenue ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="muted">No office request data yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <div class="section-title">Monthly Requests - {{ now()->year }}</div>
    <table>
        <thead>
            <tr>
                @foreach($months as $month)
                    <th class="text-right">{{ $month }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr>
                @foreach($months as $index => $month)
                    <td class="text-right">{{ number_format($monthlyRequests->get($index + 1, 0)) }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
</div>

<div class="footer">
    CedarGov Platform - Lebanese Municipalities
</div>
</body>
</html>
