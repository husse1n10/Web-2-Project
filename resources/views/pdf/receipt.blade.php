<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $serviceRequest->reference_number }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans', Arial, sans-serif; font-size:12px; color:#1a2332; background:#fff; }
        .page { padding:40px; max-width:700px; margin:0 auto; }

        .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:32px; border-bottom:2px solid #0052cc; padding-bottom:20px; }
        .logo-area h1 { font-size:20px; font-weight:900; color:#0052cc; letter-spacing:-.5px; }
        .logo-area p  { font-size:10px; color:#6b7280; margin-top:2px; }
        .receipt-badge { background:#0052cc; color:#fff; padding:6px 14px; border-radius:6px; font-size:11px; font-weight:700; letter-spacing:.05em; text-transform:uppercase; }

        .doc-title { text-align:center; margin:24px 0; }
        .doc-title h2 { font-size:18px; font-weight:700; color:#111827; }
        .doc-title .ref { display:inline-block; background:#f0f4f8; color:#0052cc; font-family:'Courier New',monospace; font-size:13px; padding:4px 14px; border-radius:20px; margin-top:6px; font-weight:700; }

        .info-grid { display:flex; gap:16px; margin-bottom:24px; }
        .info-box { flex:1; border:1px solid #e5eaf0; border-radius:8px; padding:12px 14px; background:#f8fafc; }
        .info-box .lbl { font-size:9px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#9ca3af; margin-bottom:4px; }
        .info-box .val { font-size:12px; font-weight:600; color:#111827; line-height:1.4; }

        table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        thead th { background:#0052cc; color:#fff; padding:9px 12px; font-size:10px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; text-align:left; }
        tbody td { padding:10px 12px; border-bottom:1px solid #f0f4f8; font-size:11px; }
        tbody tr:last-child td { border-bottom:none; }
        .text-right { text-align:right; }

        .totals { border:1px solid #e5eaf0; border-radius:8px; padding:14px; margin-left:auto; max-width:220px; }
        .total-row { display:flex; justify-content:space-between; font-size:11px; margin-bottom:6px; }
        .total-row.grand { border-top:1px solid #e5eaf0; padding-top:8px; margin-top:8px; }
        .total-row.grand .amount { font-size:14px; font-weight:800; color:#0052cc; }

        .stamp { text-align:center; margin:24px 0 16px; }
        .stamp-circle { display:inline-block; border:3px solid #16a34a; border-radius:50%; padding:12px 18px; color:#16a34a; font-size:14px; font-weight:900; letter-spacing:.08em; text-transform:uppercase; transform:rotate(-5deg); }

        .footer { border-top:1px solid #e5eaf0; padding-top:16px; margin-top:24px; display:flex; justify-content:space-between; align-items:flex-end; gap:18px; }
        .footer .left { font-size:9px; color:#9ca3af; line-height:1.6; }
        .footer .right { font-size:9px; color:#9ca3af; text-align:right; }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="logo-area">
            <h1>E-Services</h1>
            <p>Government Digital Services Platform</p>
        </div>
        <div class="receipt-badge">Payment Receipt</div>
    </div>

    <div class="doc-title">
        <h2>Official Payment Receipt</h2>
        <div class="ref">{{ $serviceRequest->reference_number }}</div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <div class="lbl">Citizen</div>
            <div class="val">{{ $serviceRequest->citizen->name }}<br>{{ $serviceRequest->citizen->email }}</div>
        </div>
        <div class="info-box">
            <div class="lbl">Office</div>
            <div class="val">{{ $serviceRequest->office->name }}<br>{{ $serviceRequest->office->municipality->name }}</div>
        </div>
        <div class="info-box">
            <div class="lbl">Payment Date</div>
            <div class="val">{{ $serviceRequest->updated_at->format('M d, Y') }}<br>{{ $serviceRequest->updated_at->format('H:i') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Reference</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $serviceRequest->resolved_service_name }}</td>
                <td style="color:#6b7280">{{ $serviceRequest->reference_number }}</td>
                <td class="text-right" style="font-weight:600">{{ $serviceRequest->formatted_recorded_amount }}</td>
            </tr>
        </tbody>
    </table>

    <div class="totals" style="margin-left:auto">
        <div class="total-row">
            <span style="color:#6b7280">Subtotal</span>
            <span>{{ $serviceRequest->formatted_recorded_amount }}</span>
        </div>
        <div class="total-row">
            <span style="color:#6b7280">Tax (0%)</span>
            <span>{{ $serviceRequest->formatServiceAmount(0) }}</span>
        </div>
        <div class="total-row grand">
            <span style="font-weight:700">Total Paid</span>
            <span class="amount">{{ $serviceRequest->formatted_recorded_amount }}</span>
        </div>
    </div>

    <div class="stamp">
        <div class="stamp-circle">&#10003; PAID</div>
    </div>

    @if($serviceRequest->transaction_id)
        <div style="text-align:center;font-size:10px;color:#6b7280;margin-bottom:16px">
            Transaction ID: <strong style="font-family:'Courier New',monospace">{{ $serviceRequest->transaction_id }}</strong>
            &nbsp;&middot;&nbsp; Method: {{ ucfirst($serviceRequest->payment_method ?? 'N/A') }}
        </div>
    @endif

    <div class="footer">
        <div class="left">
            This is an official receipt generated by the E-Services Government Platform.<br>
            For inquiries, contact: {{ $serviceRequest->office->email ?? 'support@e-services.local' }}<br>
            &copy; {{ date('Y') }} E-Services - All rights reserved.
        </div>
        <div class="right">
            Generated on {{ now()->format('M d, Y H:i') }}<br>
            Ref: {{ $serviceRequest->reference_number }}
        </div>
    </div>

</div>
</body>
</html>
