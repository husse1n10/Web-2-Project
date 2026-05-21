<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate — {{ $serviceRequest->reference_number }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans', Arial, sans-serif; font-size:12px; color:#1a2332; background:#fff; }
        .page {
            padding:40px; max-width:720px; margin:0 auto;
            border:8px solid #0052cc; min-height:520px;
            position:relative;
        }
        /* Inner decorative border */
        .inner-border {
            position:absolute; inset:14px;
            border:1px solid rgba(0,82,204,.2); pointer-events:none;
        }

        /* Header */
        .cert-header { text-align:center; margin-bottom:24px; padding-bottom:18px; border-bottom:2px solid rgba(0,82,204,.15); }
        .org-name { font-size:13px; font-weight:700; color:#0052cc; letter-spacing:.08em; text-transform:uppercase; margin-bottom:6px; }
        .cert-title { font-size:26px; font-weight:900; color:#0f1923; letter-spacing:-.5px; margin-bottom:4px; }
        .cert-sub   { font-size:13px; color:#6b7280; font-weight:500; }

        /* Decorative divider */
        .divider { display:flex; align-items:center; gap:12px; margin:18px 0; }
        .divider-line { flex:1; height:1px; background:linear-gradient(to right, transparent, #0052cc, transparent); }
        .divider-star { color:#0052cc; font-size:14px; }

        /* Content */
        .cert-body { text-align:center; margin:0 auto; max-width:560px; }
        .presenting { font-size:12px; color:#6b7280; margin-bottom:8px; }
        .citizen-name { font-size:22px; font-weight:900; color:#0052cc; border-bottom:2px solid #0052cc; display:inline-block; padding-bottom:4px; margin-bottom:18px; letter-spacing:-.3px; }
        .cert-text { font-size:12px; color:#374151; line-height:1.9; margin-bottom:16px; }

        /* Service highlight box */
        .service-box {
            border:1px solid rgba(0,82,204,.2); border-radius:10px;
            padding:14px 20px; margin:18px auto; max-width:400px;
            background:rgba(0,82,204,.03);
        }
        .service-box .svc-label { font-size:9px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; color:#9ca3af; margin-bottom:4px; }
        .service-box .svc-name  { font-size:15px; font-weight:800; color:#0052cc; }
        .service-box .svc-ref   { font-size:10px; color:#9ca3af; margin-top:4px; font-family:'Courier New',monospace; }

        /* Details grid */
        .details-grid { display:flex; justify-content:center; gap:24px; margin:18px 0; flex-wrap:wrap; }
        .detail-item { text-align:center; }
        .detail-item .d-lbl { font-size:9px; color:#9ca3af; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:3px; }
        .detail-item .d-val { font-size:12px; font-weight:700; color:#111827; }

        /* Signatures */
        .sig-row { display:flex; justify-content:space-around; margin-top:36px; }
        .sig-block { text-align:center; width:36%; }
        .sig-line  { border-bottom:1px solid #1a2332; height:36px; margin-bottom:5px; }
        .sig-name  { font-size:11px; font-weight:700; }
        .sig-role  { font-size:9px; color:#6b7280; }

        .cert-stamp { text-align:center; margin:6px 0; }
        .stamp-ring { display:inline-block; border:3px double #0052cc; border-radius:50%; padding:10px 16px; }
        .stamp-inner { font-size:10px; font-weight:900; color:#0052cc; letter-spacing:.1em; text-transform:uppercase; }

        /* Footer */
        .cert-footer { border-top:1px solid rgba(0,82,204,.15); margin-top:24px; padding-top:12px; font-size:9px; color:#9ca3af; display:flex; justify-content:space-between; }
    </style>
</head>
<body>
<div class="page">
    <div class="inner-border"></div>

    <div class="cert-header">
        <div class="org-name">{{ $serviceRequest->office->municipality->name }} — Government Services</div>
        <div class="cert-title">Certificate of Completion</div>
        <div class="cert-sub">Official Government Document</div>
    </div>

    <div class="divider">
        <div class="divider-line"></div>
        <span class="divider-star">★</span>
        <div class="divider-line"></div>
    </div>

    <div class="cert-body">
        <div class="presenting">This is to certify that</div>
        <div class="citizen-name">{{ $serviceRequest->citizen->name }}</div>

        <p class="cert-text">
            has successfully completed all requirements and fulfilled all obligations associated with the following
            government service request processed by <strong>{{ $serviceRequest->office->name }}</strong>.
        </p>

        <div class="service-box">
            <div class="svc-label">Service</div>
            <div class="svc-name">{{ $serviceRequest->resolved_service_name }}</div>
            <div class="svc-ref">Ref: {{ $serviceRequest->reference_number }}</div>
        </div>

        <p class="cert-text">
            This certificate confirms that all required documents have been submitted, verified, and processed
            in accordance with applicable regulations. This document serves as official proof of completion.
        </p>

        <div class="details-grid">
            <div class="detail-item">
                <div class="d-lbl">Issue Date</div>
                <div class="d-val">{{ now()->format('M d, Y') }}</div>
            </div>
            <div class="detail-item">
                <div class="d-lbl">Office</div>
                <div class="d-val">{{ Str::limit($serviceRequest->office->name, 20) }}</div>
            </div>
            <div class="detail-item">
                <div class="d-lbl">Municipality</div>
                <div class="d-val">{{ $serviceRequest->office->municipality->name }}</div>
            </div>
        </div>
    </div>

    <div class="sig-row">
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name">Head of Office</div>
            <div class="sig-role">{{ $serviceRequest->office->name }}</div>
        </div>
        <div class="cert-stamp">
            <div class="stamp-ring">
                <div class="stamp-inner">✓ CERTIFIED</div>
            </div>
        </div>
        <div class="sig-block">
            <div class="sig-line"></div>
            <div class="sig-name">Municipal Director</div>
            <div class="sig-role">{{ $serviceRequest->office->municipality->name }}</div>
        </div>
    </div>

    <div class="cert-footer">
        <span>CedarGov Platform &copy; {{ date('Y') }} — This is an official document.</span>
        <span>Generated: {{ now()->format('M d, Y H:i') }} | Ref: {{ $serviceRequest->reference_number }}</span>
    </div>

</div>
</body>
</html>
