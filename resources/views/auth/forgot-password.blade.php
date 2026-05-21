<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | {{ config('variables.templateName', 'CedarGov') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital,wght@0,400;1,400&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--cream:#F5F0E8;--ink:#1A1714;--muted:#78716C;--border:#E5E0D8;--teal:#0D9488}
        body{font-family:'Inter',system-ui,sans-serif;background:var(--cream);color:var(--ink);min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased}
        .auth-nav{height:58px;padding:0 2rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(0,0,0,.08);background:rgba(245,240,232,.88);backdrop-filter:blur(16px);flex-shrink:0}
        .auth-brand{display:flex;align-items:center;gap:.625rem;text-decoration:none;color:var(--ink)}
        .auth-brand-mark{width:34px;height:34px;border-radius:8px;overflow:hidden;box-shadow:0 6px 14px rgba(26,23,20,.22);flex-shrink:0}
        .auth-brand-mark img{width:100%;height:100%;object-fit:cover;display:block}
        .auth-brand strong{display:block;font-size:.875rem;font-weight:800;letter-spacing:-.01em;line-height:1.2}
        .auth-brand span{display:block;font-size:.6rem;color:var(--muted)}
        .auth-nav-link{font-size:.875rem;font-weight:500;color:var(--muted);text-decoration:none;padding:.4rem .875rem;border-radius:6px;transition:color .15s,background .15s}
        .auth-nav-link:hover{color:var(--ink);background:rgba(0,0,0,.05)}
        .auth-center{flex:1;display:flex;align-items:center;justify-content:center;padding:3rem 1rem;position:relative}
        .auth-center::before{content:'';position:absolute;top:-10%;left:50%;transform:translateX(-50%);width:600px;height:500px;background:radial-gradient(ellipse at 50% 30%,rgba(253,224,71,.18) 0%,transparent 65%);pointer-events:none}
        .auth-card{position:relative;z-index:1;background:#fff;border:1px solid rgba(0,0,0,.09);border-radius:16px;padding:2.5rem;width:100%;max-width:420px;box-shadow:0 2px 4px rgba(0,0,0,.04),0 8px 24px rgba(0,0,0,.07),0 24px 64px rgba(0,0,0,.05)}
        .auth-icon{width:52px;height:52px;border-radius:13px;background:#FEF3C7;display:flex;align-items:center;justify-content:center;color:#B45309;font-size:1.4rem;margin-bottom:1.25rem}
        .auth-heading{font-family:'DM Serif Display',Georgia,serif;font-style:italic;font-weight:400;font-size:1.875rem;color:var(--ink);margin-bottom:.375rem;letter-spacing:-.01em;line-height:1.15}
        .auth-sub{font-size:.875rem;color:var(--muted);margin-bottom:1.75rem;line-height:1.6}
        label{display:block;font-size:.8rem;font-weight:600;color:var(--ink);margin-bottom:.375rem}
        input[type="email"]{width:100%;height:42px;border:1px solid var(--border);border-radius:8px;padding:0 .875rem;font-size:.875rem;font-family:'Inter',sans-serif;color:var(--ink);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;margin-bottom:1.5rem}
        input::placeholder{color:#B5B0A8}
        input:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(13,148,136,.12)}
        .btn-submit{width:100%;height:44px;background:var(--ink);color:#fff;border:none;border-radius:8px;font-size:.9375rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;transition:background .15s;letter-spacing:-.01em}
        .btn-submit:hover{background:#2D2926}
        .back-link{display:block;text-align:center;margin-top:1.375rem;font-size:.875rem;color:var(--muted);text-decoration:none;transition:color .15s}
        .back-link:hover{color:var(--ink)}
        .alert-success{background:#CCFBF1;border:1px solid #99F6E4;border-radius:8px;padding:.75rem 1rem;font-size:.84rem;color:#0F766E;margin-bottom:1.25rem}
        .alert-error{background:#FFF1F2;border:1px solid #FECDD3;border-radius:8px;padding:.75rem 1rem;font-size:.84rem;color:#9F1239;margin-bottom:1.25rem}
        @media(max-width:480px){.auth-card{padding:2rem 1.5rem}.auth-nav{padding:0 1rem}}
    </style>
</head>
<body>
<nav class="auth-nav">
    <a href="{{ route('home') }}" class="auth-brand">
        <span class="auth-brand-mark"><img src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}" alt="CedarGov icon"></span>
        <div><strong>CedarGov</strong><span>Lebanon Gov Portal</span></div>
    </a>
    <a href="{{ route('login') }}" class="auth-nav-link">Back to sign in</a>
</nav>
<div class="auth-center">
    <div class="auth-card">
        <div class="auth-icon"><i class="bi bi-key"></i></div>
        <h1 class="auth-heading">Forgot password?</h1>
        <p class="auth-sub">Enter your email address and we'll send you a link to reset your password.</p>

        @if(session('status'))
            <div class="alert-success"><i class="bi bi-check-circle me-1"></i>{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-error">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autofocus required>
            <button type="submit" class="btn-submit">Send Reset Link</button>
        </form>
        <a href="{{ route('login') }}" class="back-link"><i class="bi bi-arrow-left me-1"></i>Back to sign in</a>
    </div>
</div>
</body>
</html>





