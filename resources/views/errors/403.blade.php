<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Access Denied — CedarGov Lebanon</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wdth,wght@75..100,400..700&family=Fraunces:ital,opsz,wght@0,9..144,300..900;1,9..144,300..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    :root{--navy:#060D1F;--primary:#1E4080;--red:#BE123C;--font:'Instrument Sans',system-ui,sans-serif;--font-disp:'Fraunces',Georgia,serif;}
    *{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:var(--font);min-height:100vh;background:var(--navy);display:flex;align-items:center;justify-content:center;-webkit-font-smoothing:antialiased;padding:2rem;text-align:center;overflow:hidden;position:relative;}
    .bg-grid{position:fixed;inset:0;background-image:linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;}
    .orb1{position:fixed;width:400px;height:400px;top:-150px;right:-100px;border-radius:50%;background:radial-gradient(circle,rgba(190,18,60,.3),transparent 70%);pointer-events:none;}
    .orb2{position:fixed;width:350px;height:350px;bottom:-100px;left:-80px;border-radius:50%;background:radial-gradient(circle,rgba(30,64,128,.25),transparent 70%);pointer-events:none;}
    .content{position:relative;z-index:2;max-width:500px;}
    .num{font-family:var(--font-disp);font-style:italic;font-size:clamp(5rem,18vw,9rem);font-weight:700;color:rgba(255,255,255,.06);line-height:1;letter-spacing:-.05em;margin-bottom:-1rem;}
    .icon-wrap{width:80px;height:80px;border-radius:50%;background:rgba(190,18,60,.25);border:1px solid rgba(190,18,60,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:1.8rem;color:#FDA4AF;}
    h1{font-family:var(--font-disp);font-style:italic;color:#fff;font-size:1.75rem;font-weight:700;letter-spacing:-.03em;margin-bottom:.6rem;}
    p{color:rgba(255,255,255,.45);font-size:.88rem;line-height:1.7;margin-bottom:2rem;}
    .actions{display:flex;flex-wrap:wrap;gap:.6rem;justify-content:center;}
    .btn{display:inline-flex;align-items:center;gap:.4rem;padding:.6rem 1.35rem;border-radius:9px;font-family:var(--font);font-size:.84rem;font-weight:600;text-decoration:none;transition:all .15s;cursor:pointer;border:none;}
    .btn-primary{background:var(--primary);color:#fff;}
    .btn-primary:hover{background:#162F60;box-shadow:0 4px 14px rgba(30,64,128,.5);transform:translateY(-1px);color:#fff;}
    .btn-ghost{background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);border:1.5px solid rgba(255,255,255,.15);}
    .btn-ghost:hover{background:rgba(255,255,255,.14);color:#fff;}
    </style>
</head>
<body>
<div class="bg-grid"></div>
<div class="orb1"></div>
<div class="orb2"></div>
<div class="content">
    <div class="num">403</div>
    <div class="icon-wrap"><i class="bi bi-shield-x"></i></div>
    <h1>Access Denied</h1>
    <p>You don't have permission to view this page. If you believe this is a mistake, please contact an administrator.</p>
    <div class="actions">
        <a href="{{ auth()->check() ? url()->previous() : route('home') }}" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Go Back</a>
        @auth
        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : (auth()->user()->isOfficeUser() ? route('office.dashboard') : route('citizen.dashboard')) }}" class="btn btn-primary"><i class="bi bi-house"></i> Dashboard</a>
        @else
        <a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right"></i> Sign In</a>
        @endauth
    </div>
</div>
</body>
</html>


