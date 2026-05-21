<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approval Letter - {{ $serviceRequest->reference_number }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans', Arial, sans-serif; font-size:12px; color:#1a2332; background:#fff; }
        .page { padding:48px; max-width:700px; margin:0 auto; }

        .letterhead { border-bottom:3px solid #0052cc; padding-bottom:18px; margin-bottom:28px; display:flex; justify-content:space-between; align-items:flex-end; }
        .org-name { font-size:18px; font-weight:900; color:#0052cc; }
        .org-sub  { font-size:10px; color:#6b7280; margin-top:2px; }
        .letter-date { font-size:11px; color:#6b7280; text-align:right; }

        .ref-line { background:#f0f4f8; border-left:4px solid #0052cc; padding:8px 14px; border-radius:0 6px 6px 0; margin-bottom:24px; font-size:11px; }
        .ref-line strong { color:#0052cc; }

        .recipient { margin-bottom:20px; }
        .recipient p { font-size:12px; line-height:1.6; }
        .citizen-name { font-weight:700; font-size:13px; }

        .salutation { margin-bottom:16px; font-size:12px; }

        .body-text { font-size:12px; line-height:1.8; color:#374151; margin-bottom:16px; }

        .approval-box { border:2px solid #16a34a; border-radius:10px; padding:16px; margin:20px 0; background:#f0fdf4; }
        .approval-box h3 { color:#16a34a; font-size:13px; font-weight:800; margin-bottom:10px; display:flex; align-items:center; gap:6px; }
        .approval-detail { display:flex; justify-content:space-between; margin-bottom:6px; font-size:11px; }
        .approval-detail .lbl { color:#6b7280; }
        .approval-detail .val { font-weight:700; color:#111827; }

        .conditions { margin:16px 0; }
        .conditions h4 { font-size:11px; font-weight:700; color:#374151; margin-bottom:8px; letter-spacing:.03em; text-transform:uppercase; }
        .condition-item { display:flex; gap:8px; margin-bottom:5px; font-size:11px; color:#374151; }
        .condition-item::before { content:'*'; color:#0052cc; font-weight:700; flex-shrink:0; }

        .signature-block { margin-top:36px; display:flex; justify-content:space-between; }
        .sig-side { width:45%; }
        .sig-line { border-bottom:1px solid #374151; height:32px; margin-bottom:4px; }
        .sig-name  { font-size:11px; font-weight:700; }
        .sig-title { font-size:10px; color:#6b7280; }

        .stamp-area { text-align:center; margin:8px 0; }
        .stamp { display:inline-block; border:2px solid #16a34a; border-radius:50%; padding:10px 16px; color:#16a34a; font-size:11px; font-weight:900; letter-spacing:.06em; text-transform:uppercase; transform:rotate(-8deg); }

        .footer-bar { border-top:1px solid #e5eaf0; margin-top:28px; padding-top:12px; font-size:9px; color:#9ca3af; display:flex; justify-content:space-between; }
    </style>
</head>
<body>
<div class="page">

    <div class="letterhead">
        <div>
            <div class="org-name">{{ $serviceRequest->office->name }}</div>
            <div class="org-sub">{{ $serviceRequest->office->municipality->name }} - Government Services</div>
        </div>
        <div class="letter-date">
            Date: {{ now()->format('F d, Y') }}<br>
            Doc No: {{ $serviceRequest->reference_number }}
        </div>
    </div>

    <div class="ref-line">
        <strong>RE: Approval of Service Request - {{ $serviceRequest->resolved_service_name }}</strong>
    </div>

    <div class="recipient">
        <p class="citizen-name">{{ $serviceRequest->citizen->name }}</p>
        <p style="color:#6b7280;font-size:11px">{{ $serviceRequest->citizen->email }}</p>
    </div>

    <p class="salutation">Dear {{ $serviceRequest->citizen->name }},</p>

    <p class="body-text">
        We are pleased to inform you that your application for the <strong>{{ $serviceRequest->resolved_service_name }}</strong>
        service submitted to {{ $serviceRequest->office->name }} has been reviewed and <strong style="color:#16a34a">officially approved</strong>.
    </p>

    <div class="approval-box">
        <h3>Approved Request Details</h3>
        <div class="approval-detail"><span class="lbl">Reference Number</span><span class="val">{{ $serviceRequest->reference_number }}</span></div>
        <div class="approval-detail"><span class="lbl">Service</span><span class="val">{{ $serviceRequest->resolved_service_name }}</span></div>
        <div class="approval-detail"><span class="lbl">Approval Date</span><span class="val">{{ now()->format('F d, Y') }}</span></div>
        <div class="approval-detail"><span class="lbl">Issuing Office</span><span class="val">{{ $serviceRequest->office->name }}</span></div>
        <div class="approval-detail"><span class="lbl">Estimated Completion</span><span class="val">Within {{ $serviceRequest->service->estimated_duration_days }} business day(s)</span></div>
    </div>

    <p class="body-text">
        Please ensure that all required fees are settled and any outstanding documents are submitted promptly.
        You will be notified via email and through the CedarGov platform once your documents are ready for collection or delivery.
    </p>

    <div class="conditions">
        <h4>Next Steps</h4>
        <div class="condition-item">Complete payment of the service fee ({{ $serviceRequest->formatted_service_price }}) if not yet paid.</div>
        <div class="condition-item">You will receive a notification when your document is ready.</div>
        <div class="condition-item">Track your request anytime using reference: {{ $serviceRequest->reference_number }}</div>
    </div>

    <p class="body-text">
        Thank you for using our digital government services platform. For any inquiries, please contact our office at
        <strong>{{ $serviceRequest->office->phone ?? $serviceRequest->office->email ?? 'the contact details above' }}</strong>.
    </p>

    <div class="signature-block">
        <div class="sig-side">
            <div class="sig-line"></div>
            <div class="sig-name">Authorized Signatory</div>
            <div class="sig-title">{{ $serviceRequest->office->name }}</div>
        </div>
        <div class="stamp-area">
            <div class="stamp">APPROVED</div>
        </div>
        <div class="sig-side" style="text-align:right">
            <div class="sig-line"></div>
            <div class="sig-name">Official Stamp</div>
            <div class="sig-title">{{ $serviceRequest->office->municipality->name }}</div>
        </div>
    </div>

    <div class="footer-bar">
        <span>CedarGov Platform &copy; {{ date('Y') }}</span>
        <span>Generated: {{ now()->format('M d, Y H:i') }} | Ref: {{ $serviceRequest->reference_number }}</span>
    </div>

</div>
</body>
</html>
