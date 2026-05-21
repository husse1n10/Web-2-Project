<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Track Request - CedarGov</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #0ea5e9;
            --bg-dark-1: #0b3a78;
            --bg-dark-2: #0e4ea9;
            --bg-dark-3: #1770d4;
            --font: 'Plus Jakarta Sans', sans-serif;
            --mono: 'JetBrains Mono', monospace;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font);
            min-height: 100vh;
            background: linear-gradient(145deg, var(--bg-dark-1) 0%, var(--bg-dark-2) 50%, var(--bg-dark-3) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            -webkit-font-smoothing: antialiased;
        }

        .logo-bar {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            border-radius: 11px;
            overflow: hidden;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .22);
            flex-shrink: 0;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .logo-text {
            font-family: 'Inter', sans-serif;
            line-height: 1.15;
        }

        .logo-text strong {
            display: block;
            color: #fff;
            font-weight: 800;
            font-size: .92rem;
        }

        .logo-text span {
            display: block;
            color: rgba(255, 255, 255, .75);
            font-size: .62rem;
            font-weight: 500;
            letter-spacing: .01em;
        }

        .track-card {
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
            overflow: hidden;
        }

        .ref-header {
            background: linear-gradient(135deg, #0f1923, #1e2d3d);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: .9rem;
        }

        .ref-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: rgba(255, 255, 255, .8);
            flex-shrink: 0;
        }

        .ref-title {
            color: rgba(255, 255, 255, .5);
            font-size: .72rem;
            font-weight: 600;
            margin-bottom: .2rem;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .ref-num {
            color: #fff;
            font-family: var(--mono);
            font-size: 1rem;
            font-weight: 500;
            word-break: break-all;
        }

        .card-body {
            padding: 1.5rem;
        }

        .status-block {
            display: flex;
            align-items: center;
            gap: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            padding: 1rem 1.1rem;
            margin-bottom: 1.25rem;
            border: 1.5px solid #e5eaf0;
        }

        .status-dot {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .status-label {
            font-size: .72rem;
            color: #9ca3af;
            font-weight: 500;
            margin-bottom: .15rem;
        }

        .status-value {
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -.01em;
        }

        .section-label {
            font-size: .75rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: .75rem;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .timeline {
            display: flex;
            flex-direction: column;
        }

        .tl-item {
            display: flex;
            gap: .75rem;
        }

        .tl-left {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            width: 28px;
        }

        .tl-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .68rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .tl-line {
            width: 2px;
            flex: 1;
            min-height: 16px;
            background: #e5eaf0;
            margin: 2px 0;
        }

        .tl-content {
            padding: .1rem 0 1rem;
        }

        .tl-status {
            font-size: .83rem;
            font-weight: 700;
            color: #111827;
        }

        .tl-meta {
            font-size: .72rem;
            color: #9ca3af;
            margin-top: 1px;
        }

        .tl-comment {
            font-size: .78rem;
            color: #6b7280;
            margin-top: .25rem;
            font-style: italic;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .65rem;
            margin-top: 1.25rem;
        }

        .info-item {
            background: #f8fafc;
            border-radius: 10px;
            padding: .75rem;
            border: 1px solid #eef2f7;
        }

        .info-label {
            font-size: .68rem;
            color: #9ca3af;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: .2rem;
        }

        .info-value {
            font-size: .83rem;
            font-weight: 700;
            color: #111827;
        }

        .login-cta {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f0f4f8;
            text-align: center;
        }

        .login-cta p {
            font-size: .8rem;
            color: #6b7280;
            margin-bottom: .65rem;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: var(--primary);
            color: #fff;
            padding: .55rem 1.25rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: .83rem;
            text-decoration: none;
            transition: all .15s;
            font-family: var(--font);
        }

        .btn-login:hover {
            background: #0284c7;
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(2, 132, 199, .35);
        }

        .not-found {
            text-align: center;
            padding: 2rem;
        }

        .not-found i {
            font-size: 3rem;
            color: #d1d5db;
            display: block;
            margin-bottom: .75rem;
        }

        .not-found h3 {
            font-size: 1rem;
            font-weight: 800;
            color: #374151;
            margin-bottom: .4rem;
        }

        .not-found p {
            font-size: .82rem;
            color: #9ca3af;
            line-height: 1.5;
        }

        .not-found code {
            background: #f3f4f6;
            padding: .1em .4em;
            border-radius: 4px;
            font-size: .85em;
            color: #1f2937;
        }

        .tone-pending { background: #fffbeb; color: #d97706; }
        .tone-in_review { background: #eff6ff; color: #2563eb; }
        .tone-missing_documents { background: #fef2f2; color: #dc2626; }
        .tone-approved { background: #f0fdf4; color: #16a34a; }
        .tone-rejected { background: #fef2f2; color: #dc2626; }
        .tone-completed { background: #d1fae5; color: #065f46; }
        .tone-default { background: #f3f4f6; color: #6b7280; }

        footer {
            color: rgba(255, 255, 255, .45);
            font-size: .72rem;
            margin-top: 1.5rem;
            text-align: center;
        }

        @media (max-width: 400px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .ref-num {
                font-size: .88rem;
            }
        }
    </style>
</head>
<body>
<div class="logo-bar">
    <div class="logo-icon"><img src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}" alt="CedarGov icon"></div>
    <div class="logo-text">
        <strong>CedarGov</strong>
        <span>Lebanon Gov Portal</span>
    </div>
</div>

<div class="track-card">
    @if(isset($req))
        @php
            $statusConfig = [
                'pending' => ['icon' => 'bi-hourglass-split', 'label' => 'Pending Review', 'tone' => 'pending'],
                'in_review' => ['icon' => 'bi-search', 'label' => 'In Review', 'tone' => 'in_review'],
                'missing_documents' => ['icon' => 'bi-exclamation-circle', 'label' => 'Missing Documents', 'tone' => 'missing_documents'],
                'approved' => ['icon' => 'bi-check-circle', 'label' => 'Approved', 'tone' => 'approved'],
                'rejected' => ['icon' => 'bi-x-circle', 'label' => 'Rejected', 'tone' => 'rejected'],
                'completed' => ['icon' => 'bi-patch-check', 'label' => 'Completed', 'tone' => 'completed'],
            ];
            $currentState = $statusConfig[$req->status] ?? ['icon' => 'bi-circle', 'label' => ucfirst(str_replace('_', ' ', $req->status)), 'tone' => 'default'];
        @endphp

        <div class="ref-header">
            <div class="ref-icon"><i class="bi bi-qr-code-scan"></i></div>
            <div>
                <div class="ref-title">Request Reference</div>
                <div class="ref-num">{{ $req->reference_number }}</div>
            </div>
        </div>

        <div class="card-body">
            <div class="status-block">
                <div class="status-dot tone-{{ $currentState['tone'] }}">
                    <i class="bi {{ $currentState['icon'] }}"></i>
                </div>
                <div>
                    <div class="status-label">Current Status</div>
                    <div class="status-value">{{ $currentState['label'] }}</div>
                </div>
            </div>

            @if($req->statusLogs->count())
                <div class="section-label">Status History</div>
                <div class="timeline">
                    @foreach($req->statusLogs->sortByDesc('created_at') as $log)
                        @php
                            $logState = $statusConfig[$log->to_status] ?? ['icon' => 'bi-arrow-right', 'label' => ucfirst(str_replace('_', ' ', $log->to_status)), 'tone' => 'default'];
                        @endphp
                        <div class="tl-item">
                            <div class="tl-left">
                                <div class="tl-dot tone-{{ $logState['tone'] }}">
                                    <i class="bi {{ $logState['icon'] }}"></i>
                                </div>
                                @if(!$loop->last)
                                    <div class="tl-line"></div>
                                @endif
                            </div>
                            <div class="tl-content">
                                <div class="tl-status">{{ $logState['label'] }}</div>
                                <div class="tl-meta">{{ $log->created_at->format('M d, Y \a\t H:i') }}</div>
                                @if($log->comment)
                                    <div class="tl-comment">"{{ $log->comment }}"</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Service</div>
                    <div class="info-value">{{ $req->resolved_service_name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Office</div>
                    <div class="info-value">{{ $req->office->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Submitted</div>
                    <div class="info-value">{{ $req->created_at->format('M d, Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Payment</div>
                    <div class="info-value">{{ ucfirst($req->payment_status) }}</div>
                </div>
            </div>

            <div class="login-cta">
                <p>Sign in to manage your request, upload documents, and chat with the office.</p>
                <a href="{{ route('login') }}" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In to My Account
                </a>
            </div>
        </div>
    @else
        <div class="card-body">
            <div class="not-found">
                <i class="bi bi-search"></i>
                <h3>Request Not Found</h3>
                <p>
                    The QR code reference
                    <code>{{ $reference ?? '' }}</code>
                    does not match any request in our system.
                </p>
            </div>
            <div class="login-cta">
                <a href="{{ route('login') }}" class="btn-login">
                    <i class="bi bi-house"></i> Go to Portal
                </a>
            </div>
        </div>
    @endif
</div>

<footer>&copy; {{ date('Y') }} CedarGov Government Portal</footer>
</body>
</html>

