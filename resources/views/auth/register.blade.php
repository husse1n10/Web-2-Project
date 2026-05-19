<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account | {{ config('variables.templateName', 'CedarGov') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital,wght@0,400;1,400&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --cream:#F5F0E8; --ink:#1A1714; --muted:#78716C; --border:#E5E0D8; --teal:#0D9488; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--cream);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        .auth-nav {
            height: 58px;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(0,0,0,0.08);
            background: rgba(245,240,232,0.88);
            backdrop-filter: blur(16px);
            flex-shrink: 0;
        }
        .auth-brand { display:flex; align-items:center; gap:.625rem; text-decoration:none; color:var(--ink); }
        .auth-brand-mark{width:34px;height:34px;border-radius:8px;overflow:hidden;box-shadow:0 6px 14px rgba(26,23,20,.22);flex-shrink:0}
        .auth-brand-mark img{width:100%;height:100%;object-fit:cover;display:block}
        .auth-brand strong { display:block; font-size:.875rem; font-weight:800; letter-spacing:-.01em; line-height:1.2; }
        .auth-brand span { display:block; font-size:.6rem; color:var(--muted); }
        .auth-nav-link { font-size:.875rem; font-weight:500; color:var(--muted); text-decoration:none; padding:.4rem .875rem; border-radius:6px; transition:color .15s,background .15s; }
        .auth-nav-link:hover { color:var(--ink); background:rgba(0,0,0,0.05); }

        .auth-center {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2.5rem 1rem 3rem;
            position: relative;
        }
        .auth-center::before {
            content:'';
            position:absolute;
            top:-10%; left:50%;
            transform:translateX(-50%);
            width:700px; height:500px;
            background:radial-gradient(ellipse at 50% 30%, rgba(253,224,71,.18) 0%, transparent 65%);
            pointer-events:none;
        }

        .auth-card {
            position: relative;
            z-index: 1;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.09);
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 520px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04), 0 8px 24px rgba(0,0,0,0.07), 0 24px 64px rgba(0,0,0,0.05);
        }

        .auth-heading { font-family:'DM Serif Display',Georgia,serif; font-style:italic; font-weight:400; font-size:1.875rem; color:var(--ink); margin-bottom:.375rem; letter-spacing:-.01em; line-height:1.15; }
        .auth-sub { font-size:.875rem; color:var(--muted); margin-bottom:1.75rem; line-height:1.5; }

        .social-row { display:flex; gap:.625rem; margin-bottom:.5rem; }
        .social-btn { flex:1; display:flex; align-items:center; justify-content:center; gap:.5rem; height:40px; border:1px solid var(--border); border-radius:8px; background:#fff; color:var(--ink); font-size:.84rem; font-weight:500; text-decoration:none; transition:background .14s,border-color .14s; font-family:'Inter',sans-serif; }
        .social-btn:hover { background:var(--cream); border-color:#CCC7BF; color:var(--ink); }

        .auth-divider { display:flex; align-items:center; gap:.75rem; margin:1.25rem 0; font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.09em; color:#B5B0A8; }
        .auth-divider::before,.auth-divider::after { content:''; flex:1; height:1px; background:var(--border); }

        .form-row { display:grid; grid-template-columns:1fr 1fr; gap:.875rem; margin-bottom:1rem; }
        .field { margin-bottom:1rem; }
        .field:last-child { margin-bottom:0; }

        label { display:block; font-size:.8rem; font-weight:600; color:var(--ink); margin-bottom:.375rem; }
        .form-hint { font-size:.72rem; color:var(--muted); margin-top:.3rem; }

        input[type="email"],
        input[type="text"],
        input[type="password"],
        input[type="tel"],
        input[type="file"] {
            width:100%; height:42px; border:1px solid var(--border); border-radius:8px;
            padding:0 .875rem; font-size:.875rem; font-family:'Inter',sans-serif;
            color:var(--ink); background:#fff; outline:none;
            transition:border-color .15s,box-shadow .15s;
        }
        input[type="file"] { height:auto; padding:.5rem .875rem; cursor:pointer; }
        input::placeholder { color:#B5B0A8; }
        input:focus { border-color:var(--teal); box-shadow:0 0 0 3px rgba(13,148,136,.12); }

        .pwd-wrap { position:relative; }
        .pwd-wrap input { padding-right:2.75rem; }
        .pwd-toggle { position:absolute; right:.75rem; top:50%; transform:translateY(-50%); background:none; border:none; color:#B5B0A8; cursor:pointer; padding:0; font-size:.95rem; line-height:1; transition:color .14s; }
        .pwd-toggle:hover { color:var(--ink); }

        .check-label { display:flex; align-items:flex-start; gap:.5rem; font-size:.84rem; color:var(--muted); cursor:pointer; line-height:1.5; margin-bottom:1.5rem; }
        .check-label input[type="checkbox"] { width:16px; height:16px; border-radius:4px; cursor:pointer; accent-color:var(--teal); flex-shrink:0; margin-top:2px; }
        .check-label a { color:var(--teal); font-weight:600; text-decoration:none; }
        .check-label a:hover { text-decoration:underline; }

        .btn-submit { width:100%; height:44px; background:var(--ink); color:#fff; border:none; border-radius:8px; font-size:.9375rem; font-weight:700; font-family:'Inter',sans-serif; cursor:pointer; transition:background .15s; letter-spacing:-.01em; }
        .btn-submit:hover { background:#2D2926; }

        .auth-footer-link { text-align:center; margin-top:1.375rem; font-size:.875rem; color:var(--muted); }
        .auth-footer-link a { color:var(--ink); font-weight:700; text-decoration:none; }
        .auth-footer-link a:hover { text-decoration:underline; }

        .alert-error { background:#FFF1F2; border:1px solid #FECDD3; border-radius:8px; padding:.75rem 1rem; font-size:.84rem; color:#9F1239; margin-bottom:1.25rem; }
        .extract-status { display:none; margin-top:.55rem; border:1px solid transparent; border-radius:8px; padding:.58rem .72rem; font-size:.78rem; font-weight:500; line-height:1.45; }

        @media (max-width:560px) {
            .form-row { grid-template-columns:1fr; }
            .auth-card { padding:2rem 1.5rem; }
            .auth-nav { padding:0 1rem; }
        }
    </style>
</head>
<body>

<nav class="auth-nav">
    <a href="{{ route('home') }}" class="auth-brand">
        <span class="auth-brand-mark"><img src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}" alt="CedarGov icon"></span>
        <div>
            <strong>CedarGov</strong>
            <span>Lebanon Gov Portal</span>
        </div>
    </a>
    <a href="{{ route('login') }}" class="auth-nav-link">Sign in</a>
</nav>

<div class="auth-center">
    <div class="auth-card">

        <h1 class="auth-heading">Create your account</h1>
        <p class="auth-sub">Register to access Lebanese municipal e-services online</p>

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        @if(Route::has('social.redirect'))
        <div class="social-row">
            <a href="{{ route('social.redirect', 'google') }}" class="social-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                Google
            </a>
            <a href="{{ route('social.redirect', 'github') }}" class="social-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0 0 24 12c0-6.63-5.37-12-12-12z"/>
                </svg>
                GitHub
            </a>
        </div>
        <div class="auth-divider">or register with email</div>
        @endif

        <form action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-row">
                <div>
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="John" required>
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="Doe" required>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                </div>
                <div>
                    <label for="phone">Phone <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+961 7x xxx xxx">
                </div>
            </div>

            <div class="field">
                <label for="national_id">National ID Number</label>
                <input type="text" id="national_id" name="national_id" value="{{ old('national_id') }}" placeholder="LB-XXXXXXXXX" required>
                <p class="form-hint"><i class="bi bi-shield-lock"></i> Encrypted and stored securely for identity verification</p>
            </div>

            <div class="field">
                <label for="national_id_document">National ID Document <span style="font-weight:400;color:var(--muted);">(optional)</span></label>
                <input type="file" id="national_id_document" name="national_id_document" accept=".jpg,.jpeg,.png,.pdf">
                <p class="form-hint">JPG, PNG or PDF &mdash; max 5 MB</p>
                <div id="registerOcrStatus" class="extract-status" role="status" aria-live="polite"></div>
            </div>

            <div class="form-row" style="margin-bottom:1.5rem;">
                <div>
                    <label for="password">Password</label>
                    <div class="pwd-wrap">
                        <input type="password" id="password" name="password" placeholder="••••••••••••" required>
                        <button class="pwd-toggle" type="button" onclick="togglePwd('password','ep1')"><i class="bi bi-eye" id="ep1"></i></button>
                    </div>
                </div>
                <div>
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="pwd-wrap">
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••••••" required>
                        <button class="pwd-toggle" type="button" onclick="togglePwd('password_confirmation','ep2')"><i class="bi bi-eye" id="ep2"></i></button>
                    </div>
                </div>
            </div>

            <label class="check-label">
                <input type="checkbox" name="terms" required>
                I agree to the <a href="javascript:void(0);">Terms of Service</a>&nbsp;and&nbsp;<a href="javascript:void(0);">Privacy Policy</a>
            </label>

            <button type="submit" class="btn-submit">Create Account</button>
        </form>

        <p class="auth-footer-link">
            Already have an account? <a href="{{ route('login') }}">Sign in instead</a>
        </p>
    </div>
</div>

<script>
function togglePwd(id, iconId) {
    const i = document.getElementById(id);
    const ic = document.getElementById(iconId);
    i.type = i.type === 'password' ? 'text' : 'password';
    ic.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

const idDocumentInput = document.getElementById('national_id_document');
const nationalIdInput = document.getElementById('national_id');
const firstNameInput = document.getElementById('first_name');
const lastNameInput = document.getElementById('last_name');
const ocrStatus = document.getElementById('registerOcrStatus');
const extractEndpoint = '{{ route('register.id-extract') }}';
const csrfToken = '{{ csrf_token() }}';

let extractionRunId = 0;

function setExtractionStatus(message, type = 'info') {
    if (!ocrStatus) return;

    const palette = {
        info: ['#EFF6FF', '#1E4080', '#BFDBFE'],
        success: ['#ECFDF5', '#0D7A4E', '#A7F3D0'],
        warn: ['#FFF7ED', '#9A3412', '#FED7AA'],
        error: ['#FFF1F2', '#9F1239', '#FECDD3'],
    };

    const [bg, color, border] = palette[type] || palette.info;
    ocrStatus.style.display = 'block';
    ocrStatus.style.background = bg;
    ocrStatus.style.color = color;
    ocrStatus.style.borderColor = border;
    ocrStatus.textContent = message;
}

async function extractIdData(file) {
    if (!file) return;

    const myRunId = ++extractionRunId;
    setExtractionStatus('Reading ID document...', 'info');

    const formData = new FormData();
    formData.append('national_id_document', file);

    try {
        const response = await fetch(extractEndpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
        });

        const payload = await response.json().catch(() => ({}));
        if (myRunId !== extractionRunId) return;

        if (!response.ok) {
            setExtractionStatus(payload.message || 'Could not extract fields from this file. Please fill manually.', 'warn');
            return;
        }

        if (payload?.data?.national_id && nationalIdInput) {
            nationalIdInput.value = payload.data.national_id;
        }
        if (payload?.data?.first_name && firstNameInput) {
            firstNameInput.value = payload.data.first_name;
        }
        if (payload?.data?.last_name && lastNameInput) {
            lastNameInput.value = payload.data.last_name;
        }

        setExtractionStatus(payload.message || 'ID fields extracted successfully.', 'success');
    } catch (error) {
        setExtractionStatus('Extraction request failed. Please check your connection and try again.', 'error');
    }
}

idDocumentInput?.addEventListener('change', () => {
    const file = idDocumentInput.files?.[0];
    if (file) {
        extractIdData(file);
    }
});
</script>
</body>
</html>




