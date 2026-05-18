<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | {{ config('variables.templateName', 'CedarGov') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    {{-- Design System --}}
    <link href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: time() }}" rel="stylesheet">

    <style>
        /* Role-aware dashboard theming */
        body {
            background: var(--es-bg);
        }

        body.es-role-admin {
            --es-bg: #F3F6FB;
            --es-surface: #FFFFFF;
            --es-border: #D9E1EC;
            --es-border-soft: #E7EDF5;
            --es-input-border: #94A3B8;
            --es-text: #0F172A;
            --es-muted: #64748B;
            --es-subtle: #94A3B8;
            --es-primary: #2563EB;
            --es-primary-dk: #1D4ED8;
            --es-primary-s: #DBEAFE;
            --es-primary-m: #BFDBFE;
            --es-sky: #2563EB;
            --es-sky-s: #DBEAFE;
            --es-amber: #D97706;
            --es-amber-s: #FFEDD5;
            --es-emerald: #059669;
            --es-emerald-s: #D1FAE5;
            --es-rose: #E11D48;
            --es-rose-s: #FFE4E6;
            --shadow-card: 0 8px 22px rgba(15, 23, 42, 0.06), 0 2px 6px rgba(15, 23, 42, 0.04);
            --brand-mark-bg: #0F172A;
            font-family: 'Public Sans', 'Inter', system-ui, -apple-system, sans-serif;
        }

        body.es-role-citizen {
            --es-bg: #F4F8FF;
            --es-surface: #FFFFFF;
            --es-border: #D7E5F6;
            --es-border-soft: #E8F0FA;
            --es-input-border: #93B4D8;
            --es-text: #0F172A;
            --es-muted: #64748B;
            --es-subtle: #94A3B8;
            --es-primary: #0EA5E9;
            --es-primary-dk: #0284C7;
            --es-primary-s: #E0F2FE;
            --es-primary-m: #BAE6FD;
            --es-sky: #0EA5E9;
            --es-sky-s: #E0F2FE;
            --es-amber: #D97706;
            --es-amber-s: #FFEDD5;
            --es-emerald: #059669;
            --es-emerald-s: #D1FAE5;
            --es-rose: #E11D48;
            --es-rose-s: #FFE4E6;
            --shadow-card: 0 10px 30px rgba(15, 23, 42, 0.06), 0 3px 10px rgba(15, 23, 42, 0.04);
            --brand-mark-bg: linear-gradient(145deg, #0EA5E9 0%, #2563EB 100%);
            font-family: 'Inter', 'Public Sans', system-ui, -apple-system, sans-serif;
        }

        body.es-role-office_user {
            --es-bg: #F6F8FC;
            --es-surface: #FFFFFF;
            --es-border: #D8E1EF;
            --es-border-soft: #E9EEF7;
            --es-input-border: #94A3B8;
            --es-text: #0F172A;
            --es-muted: #64748B;
            --es-subtle: #94A3B8;
            --es-primary: #2563EB;
            --es-primary-dk: #1D4ED8;
            --es-primary-s: #DBEAFE;
            --es-primary-m: #BFDBFE;
            --es-sky: #0EA5E9;
            --es-sky-s: #E0F2FE;
            --es-amber: #D97706;
            --es-amber-s: #FFEDD5;
            --es-emerald: #059669;
            --es-emerald-s: #D1FAE5;
            --es-rose: #E11D48;
            --es-rose-s: #FFE4E6;
            --shadow-card: 0 10px 26px rgba(15, 23, 42, 0.07), 0 2px 8px rgba(15, 23, 42, 0.04);
            --brand-mark-bg: linear-gradient(145deg, #1D4ED8 0%, #0EA5E9 100%);
            font-family: 'Inter', 'Public Sans', system-ui, -apple-system, sans-serif;
        }

        body.es-role-guest {
            --brand-mark-bg: #1A1714;
        }

        .es-sidebar {
            background: var(--es-surface);
            border-right: 1px solid var(--es-border);
        }

        .es-sidebar-brand {
            border-bottom: 1px solid var(--es-border);
        }

        .es-brand-mark {
            border-radius: 8px;
            width: 34px;
            height: 34px;
            overflow: hidden;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.85);
            flex-shrink: 0;
        }

        .es-brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .es-brand-name {
            font-size: 0.875rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            font-family: 'Inter', system-ui, sans-serif;
        }

        .es-brand-sub {
            font-size: 0.6rem;
            letter-spacing: 0.01em;
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* Nav links */
        .es-nav-link {
            color: var(--es-muted);
            border-radius: 7px;
            margin: 0.0625rem 0.625rem;
            font-size: 0.875rem;
        }
        .es-nav-link:hover {
            background: var(--es-bg);
            color: var(--es-text);
        }
        .es-nav-link.active {
            background: var(--es-primary-s);
            color: var(--es-primary);
        }

        /* Topbar */
        .es-topbar {
            background: var(--es-surface);
            background: color-mix(in srgb, var(--es-surface) 88%, transparent);
            border-bottom: 1px solid var(--es-border);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .es-content { background: var(--es-bg); }

        .card {
            border-color: var(--es-border);
            box-shadow: var(--shadow-card);
        }

        .card-header {
            border-bottom-color: var(--es-border-soft);
        }

        .table th {
            background: var(--es-bg);
            border-bottom-color: var(--es-border);
            color: var(--es-subtle);
        }
        .table td {
            border-bottom-color: var(--es-border-soft);
            color: var(--es-text);
        }
        .table-hover tbody tr:hover > td {
            background: var(--es-bg);
        }

        .es-footer {
            border-top-color: var(--es-border);
            background: var(--es-surface);
        }

        .btn-primary {
            background: var(--es-primary);
            border-color: var(--es-primary);
        }
        .btn-primary:hover {
            background: var(--es-primary-dk);
            border-color: var(--es-primary-dk);
        }

        .breadcrumb {
            background: transparent;
        }

        .es-empty-state {
            text-align: center;
            padding: 2rem 1rem;
        }

        .es-empty-icon {
            width: 3rem;
            height: 3rem;
            border-radius: .85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: .6rem;
            background: var(--es-primary-s);
            color: var(--es-primary);
            border: 1px solid var(--es-primary-m);
            font-size: 1.1rem;
        }

        .es-empty-title {
            font-size: .88rem;
            font-weight: 700;
            color: var(--es-text);
            margin-bottom: .2rem;
        }

        .es-empty-copy {
            font-size: .8rem;
            color: var(--es-muted);
            margin-bottom: .85rem;
        }

        .es-empty-action {
            min-width: 7.4rem;
        }

        /* Sneat-style admin refinements (admin only) */
        body.es-role-admin .es-wrapper {
            letter-spacing: 0.01px;
        }

        body.es-role-admin .es-sidebar {
            box-shadow: 1px 0 0 rgba(15, 23, 42, 0.03);
        }

        /* ═══════════════════════════════════════════════════════
           CITIZEN PORTAL — PREMIUM DESIGN SYSTEM
           Glass-morphism · Dark sidebar · Animated backgrounds
           ═══════════════════════════════════════════════════════ */

        body.es-role-citizen .es-wrapper {
            letter-spacing: .01px;
        }

        /* ── Animated mesh background ── */
        body.es-role-citizen .es-content {
            position: relative;
            background:
                radial-gradient(ellipse at 15% -5%, rgba(56,189,248,0.18) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 110%, rgba(99,102,241,0.12) 0%, transparent 55%),
                radial-gradient(ellipse at 50% 50%, rgba(16,185,129,0.05) 0%, transparent 65%),
                var(--es-bg);
            overflow: hidden;
        }

        body.es-role-citizen .es-content::before,
        body.es-role-citizen .es-content::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            filter: blur(80px);
        }

        body.es-role-citizen .es-content::before {
            width: 500px;
            height: 500px;
            top: -120px;
            right: -80px;
            background: radial-gradient(circle, rgba(14,165,233,0.12) 0%, rgba(99,102,241,0.06) 50%, transparent 70%);
            animation: citizenOrbitA 25s ease-in-out infinite;
        }

        body.es-role-citizen .es-content::after {
            width: 400px;
            height: 400px;
            bottom: -100px;
            left: -60px;
            background: radial-gradient(circle, rgba(16,185,129,0.1) 0%, rgba(14,165,233,0.05) 50%, transparent 70%);
            animation: citizenOrbitB 30s ease-in-out infinite;
        }

        body.es-role-citizen .es-content > *:not(.modal):not(.modal-backdrop) {
            position: relative;
            z-index: 1;
        }

        body.es-role-citizen .es-content > .modal {
            z-index: 1060;
        }

        @keyframes citizenOrbitA {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-40px, 60px) scale(1.1); }
            66% { transform: translate(30px, -30px) scale(0.95); }
        }

        @keyframes citizenOrbitB {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, -40px) scale(1.08); }
        }

        /* ── Dark glass sidebar ── */
        body.es-role-citizen .es-sidebar {
            background: linear-gradient(180deg, #0B1120 0%, #111B2E 50%, #0F1729 100%);
            border-right: 1px solid rgba(255,255,255,0.06);
            box-shadow: 4px 0 24px rgba(0,0,0,0.3), 1px 0 0 rgba(255,255,255,0.04);
        }

        body.es-role-citizen .es-sidebar-brand {
            border-bottom: 1px solid rgba(255,255,255,0.06) !important;
        }

        body.es-role-citizen .es-brand-name {
            color: #F1F5F9 !important;
        }

        body.es-role-citizen .es-brand-sub {
            color: #64748B !important;
        }

        body.es-role-citizen .es-nav-section {
            color: rgba(148,163,184,0.6);
            letter-spacing: .1em;
            font-size: .62rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        body.es-role-citizen .es-nav-link {
            margin: .15rem .65rem;
            border: 1px solid transparent;
            border-radius: .6rem;
            padding: .58rem .9rem;
            font-weight: 500;
            color: #94A3B8;
            transition: all .22s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-citizen .es-nav-link i {
            color: #64748B;
            transition: color .22s ease;
        }

        body.es-role-citizen .es-nav-link:hover {
            transform: translateX(3px);
            color: #E2E8F0;
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        body.es-role-citizen .es-nav-link:hover i {
            color: #38BDF8;
        }

        body.es-role-citizen .es-nav-link.active {
            color: #FFFFFF;
            border-color: rgba(56,189,248,0.25);
            background: linear-gradient(135deg, rgba(14,165,233,0.2) 0%, rgba(99,102,241,0.15) 100%);
            box-shadow: 0 8px 24px rgba(14,165,233,0.2), inset 0 1px 0 rgba(255,255,255,0.06);
        }

        body.es-role-citizen .es-nav-link.active i {
            color: #38BDF8;
        }

        body.es-role-citizen .es-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            border-radius: 0 3px 3px 0;
            background: linear-gradient(180deg, #38BDF8, #818CF8);
            box-shadow: 0 0 12px rgba(56,189,248,0.5);
        }

        body.es-role-citizen .es-nav-badge {
            background: linear-gradient(135deg, #0EA5E9, #6366F1) !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(14,165,233,0.4);
        }

        body.es-role-citizen .es-sidebar .es-nav-link[type="submit"],
        body.es-role-citizen .es-sidebar button.es-nav-link {
            color: #94A3B8;
        }

        body.es-role-citizen .es-sidebar .es-nav-link[type="submit"]:hover,
        body.es-role-citizen .es-sidebar button.es-nav-link:hover {
            color: #FCA5A5;
            background: rgba(239,68,68,0.1);
            border-color: rgba(239,68,68,0.2);
        }

        /* ── Glass topbar ── */
        body.es-role-citizen .es-topbar {
            border-bottom: 1px solid rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(20px) saturate(1.6);
            -webkit-backdrop-filter: blur(20px) saturate(1.6);
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-citizen .es-topbar.is-scrolled {
            border-bottom-color: rgba(203,213,225,0.5);
            box-shadow: 0 8px 32px rgba(15,23,42,0.08);
            background: rgba(255,255,255,0.72);
        }

        body.es-role-citizen .es-topbar-title {
            color: #0F172A;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #0F172A 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        body.es-role-citizen .es-topbar-btn {
            transition: all .2s ease;
        }

        body.es-role-citizen .es-topbar-btn:hover {
            background: rgba(14,165,233,0.08);
            color: #0EA5E9;
        }

        /* ── Avatar glow ── */
        body.es-role-citizen .es-avatar {
            color: #0369A1;
            background: linear-gradient(135deg, #E0F2FE 0%, #BAE6FD 100%);
            border: 2px solid rgba(56,189,248,0.4);
            box-shadow: 0 0 0 3px rgba(14,165,233,0.1), 0 8px 20px rgba(14,165,233,0.2);
            transition: all .3s ease;
        }

        body.es-role-citizen .es-avatar:hover {
            box-shadow: 0 0 0 4px rgba(14,165,233,0.15), 0 12px 28px rgba(14,165,233,0.3);
            transform: scale(1.05);
        }

        /* ── Glass cards ── */
        body.es-role-citizen .card {
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(12px) saturate(1.4);
            -webkit-backdrop-filter: blur(12px) saturate(1.4);
            box-shadow: 0 4px 16px rgba(15,23,42,0.04), 0 1px 3px rgba(15,23,42,0.02);
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-citizen .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(15,23,42,0.08), 0 8px 16px rgba(14,165,233,0.06);
            border-color: rgba(14,165,233,0.2);
        }

        body.es-role-citizen .card-header {
            background: rgba(255,255,255,0.3);
            border-bottom: 1px solid rgba(226,232,240,0.6);
        }

        /* ── Gradient primary button with glow ── */
        body.es-role-citizen .btn-primary {
            background: linear-gradient(135deg, #0EA5E9 0%, #6366F1 100%);
            border: none;
            box-shadow: 0 4px 14px rgba(14,165,233,0.3), inset 0 1px 0 rgba(255,255,255,0.15);
            transition: all .25s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }

        body.es-role-citizen .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.2) 50%, transparent 100%);
            transition: left .5s ease;
        }

        body.es-role-citizen .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(14,165,233,0.4), 0 0 0 2px rgba(14,165,233,0.1);
            background: linear-gradient(135deg, #0284C7 0%, #4F46E5 100%);
        }

        body.es-role-citizen .btn-primary:hover::before {
            left: 100%;
        }

        body.es-role-citizen .btn-outline-primary {
            border: 1.5px solid rgba(14,165,233,0.4);
            color: #0284C7;
            background: rgba(224,242,254,0.3);
            transition: all .22s ease;
        }

        body.es-role-citizen .btn-outline-primary:hover {
            background: linear-gradient(135deg, #0EA5E9 0%, #6366F1 100%);
            border-color: transparent;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(14,165,233,0.3);
        }

        /* ── Enhanced table ── */
        body.es-role-citizen .table {
            --bs-table-bg: transparent;
        }

        body.es-role-citizen .table th {
            background: rgba(248,250,252,0.6);
            backdrop-filter: blur(4px);
            font-size: .67rem;
            letter-spacing: .08em;
            color: #94A3B8;
        }

        body.es-role-citizen .table-hover tbody tr {
            transition: all .2s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-citizen .table-hover tbody tr:hover {
            transform: scale(1.005);
            background: rgba(224,242,254,0.25) !important;
        }

        body.es-role-citizen .table-hover tbody tr:hover > td {
            background: transparent;
        }

        /* ── Form inputs glass ── */
        body.es-role-citizen .form-control,
        body.es-role-citizen .form-select {
            background: rgba(255,255,255,0.9);
            border: 1px solid #93B4D8;
            backdrop-filter: blur(4px);
            transition: all .25s ease;
        }

        body.es-role-citizen .form-control:focus,
        body.es-role-citizen .form-select:focus {
            background: rgba(255,255,255,0.9);
            border-color: #0EA5E9;
            box-shadow: 0 0 0 4px rgba(14,165,233,0.1), 0 4px 12px rgba(14,165,233,0.08);
        }

        /* ── Staggered reveal animation ── */
        body.es-role-citizen .citizen-reveal,
        body.es-role-citizen [data-citizen-reveal] {
            opacity: 0;
            transform: translateY(16px) scale(0.98);
            transition: opacity .5s cubic-bezier(.4,0,.2,1), transform .5s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-citizen .citizen-reveal.is-visible,
        body.es-role-citizen [data-citizen-reveal].is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        /* Stagger children */
        body.es-role-citizen .citizen-reveal:nth-child(2) { transition-delay: .06s; }
        body.es-role-citizen .citizen-reveal:nth-child(3) { transition-delay: .12s; }
        body.es-role-citizen .citizen-reveal:nth-child(4) { transition-delay: .18s; }
        body.es-role-citizen .citizen-reveal:nth-child(5) { transition-delay: .24s; }
        body.es-role-citizen .citizen-reveal:nth-child(6) { transition-delay: .30s; }

        /* ── Utility: shimmer sweep ── */
        @keyframes citizenShimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        /* ── Utility: gradient text ── */
        body.es-role-citizen .citizen-gradient-text {
            background: linear-gradient(135deg, #0EA5E9 0%, #6366F1 50%, #8B5CF6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Scrollbar styling for citizen ── */
        body.es-role-citizen .es-sidebar::-webkit-scrollbar {
            width: 4px;
        }
        body.es-role-citizen .es-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }
        body.es-role-citizen .es-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.1);
            border-radius: 4px;
        }
        body.es-role-citizen .es-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.2);
        }

        /* ── Footer ── */
        body.es-role-citizen .es-footer {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(8px);
            border-top-color: rgba(226,232,240,0.5);
        }

        /* ── Reduced motion ── */
        @media (prefers-reduced-motion: reduce) {
            body.es-role-citizen .es-content::before,
            body.es-role-citizen .es-content::after,
            body.es-role-citizen .card,
            body.es-role-citizen .btn,
            body.es-role-citizen .es-nav-link,
            body.es-role-citizen .citizen-reveal,
            body.es-role-citizen [data-citizen-reveal] {
                animation: none !important;
                transition: none !important;
            }
        }

        /* ═══════════════════════════════════════════════════════
           OFFICE PANEL — PREMIUM DESIGN SYSTEM
           Dark sidebar · Glass cards · Gradient accents
           ═══════════════════════════════════════════════════════ */

        body.es-role-office_user .es-wrapper {
            letter-spacing: .01px;
        }

        body.es-role-office_user .es-content {
            position: relative;
            background:
                radial-gradient(ellipse at 10% -5%, rgba(59,130,246,0.16) 0%, transparent 50%),
                radial-gradient(ellipse at 90% 110%, rgba(14,165,233,0.1) 0%, transparent 50%),
                var(--es-bg);
            overflow: hidden;
        }

        body.es-role-office_user .es-content::before {
            content: '';
            position: fixed;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            top: -100px;
            right: -60px;
            background: radial-gradient(circle, rgba(37,99,235,0.08) 0%, transparent 70%);
            animation: officeOrbit 22s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
            filter: blur(60px);
        }

        body.es-role-office_user .es-content > *:not(.modal):not(.modal-backdrop) {
            position: relative;
            z-index: 1;
        }

        body.es-role-office_user .es-content > .modal {
            z-index: 1060;
        }

        @keyframes officeOrbit {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-30px, 40px) scale(1.05); }
        }

        /* ── Dark sidebar ── */
        body.es-role-office_user .es-sidebar {
            background: linear-gradient(180deg, #0C1222 0%, #131D33 50%, #0E1628 100%);
            border-right: 1px solid rgba(255,255,255,0.05);
            box-shadow: 4px 0 24px rgba(0,0,0,0.25), 1px 0 0 rgba(255,255,255,0.03);
        }

        body.es-role-office_user .es-sidebar-brand {
            border-bottom: 1px solid rgba(255,255,255,0.05) !important;
        }

        body.es-role-office_user .es-brand-name {
            color: #F1F5F9 !important;
        }

        body.es-role-office_user .es-brand-sub {
            color: #64748B !important;
        }

        body.es-role-office_user .es-nav-section {
            color: rgba(148,163,184,0.55);
            letter-spacing: .1em;
            font-size: .62rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        body.es-role-office_user .es-nav-link {
            margin: .15rem .65rem;
            border: 1px solid transparent;
            border-radius: .6rem;
            padding: .58rem .9rem;
            font-weight: 500;
            color: #94A3B8;
            transition: all .22s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-office_user .es-nav-link i {
            color: #64748B;
            transition: color .22s ease;
        }

        body.es-role-office_user .es-nav-link:hover {
            transform: translateX(3px);
            color: #E2E8F0;
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.07);
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        }

        body.es-role-office_user .es-nav-link:hover i {
            color: #60A5FA;
        }

        body.es-role-office_user .es-nav-link.active {
            color: #FFFFFF;
            border-color: rgba(59,130,246,0.25);
            background: linear-gradient(135deg, rgba(37,99,235,0.2) 0%, rgba(14,165,233,0.15) 100%);
            box-shadow: 0 8px 24px rgba(37,99,235,0.18), inset 0 1px 0 rgba(255,255,255,0.05);
        }

        body.es-role-office_user .es-nav-link.active i {
            color: #60A5FA;
        }

        body.es-role-office_user .es-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            border-radius: 0 3px 3px 0;
            background: linear-gradient(180deg, #3B82F6, #06B6D4);
            box-shadow: 0 0 12px rgba(59,130,246,0.5);
        }

        body.es-role-office_user .es-nav-badge {
            background: linear-gradient(135deg, #2563EB, #0EA5E9) !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(37,99,235,0.4);
        }

        body.es-role-office_user .es-sidebar .es-nav-link[type="submit"],
        body.es-role-office_user .es-sidebar button.es-nav-link {
            color: #94A3B8;
        }

        body.es-role-office_user .es-sidebar .es-nav-link[type="submit"]:hover,
        body.es-role-office_user .es-sidebar button.es-nav-link:hover {
            color: #FCA5A5;
            background: rgba(239,68,68,0.1);
            border-color: rgba(239,68,68,0.2);
        }

        /* ── Glass topbar ── */
        body.es-role-office_user .es-topbar {
            border-bottom: 1px solid rgba(255,255,255,0.35);
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(18px) saturate(1.5);
            -webkit-backdrop-filter: blur(18px) saturate(1.5);
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-office_user .es-topbar.is-scrolled {
            border-bottom-color: rgba(203,213,225,0.45);
            box-shadow: 0 8px 28px rgba(15,23,42,0.07);
            background: rgba(255,255,255,0.68);
        }

        body.es-role-office_user .es-topbar-title {
            color: #0F172A;
            font-size: 1.04rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #0F172A 0%, #334155 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        body.es-role-office_user .es-avatar {
            color: #1D4ED8;
            background: linear-gradient(135deg, #DBEAFE 0%, #C7D2FE 100%);
            border: 2px solid rgba(59,130,246,0.35);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.08), 0 8px 20px rgba(37,99,235,0.18);
            transition: all .3s ease;
        }

        body.es-role-office_user .es-avatar:hover {
            box-shadow: 0 0 0 4px rgba(37,99,235,0.12), 0 12px 28px rgba(37,99,235,0.25);
            transform: scale(1.05);
        }

        /* ── Glass cards ── */
        body.es-role-office_user .card {
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.45);
            background: rgba(255,255,255,0.65);
            backdrop-filter: blur(12px) saturate(1.3);
            -webkit-backdrop-filter: blur(12px) saturate(1.3);
            box-shadow: 0 4px 16px rgba(15,23,42,0.04), 0 1px 3px rgba(15,23,42,0.02);
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-office_user .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(15,23,42,0.07), 0 8px 16px rgba(37,99,235,0.05);
            border-color: rgba(37,99,235,0.18);
        }

        body.es-role-office_user .card-header {
            background: rgba(255,255,255,0.25);
            border-bottom: 1px solid rgba(226,232,240,0.55);
        }

        /* ── Gradient primary button ── */
        body.es-role-office_user .btn-primary {
            background: linear-gradient(135deg, #2563EB 0%, #0EA5E9 100%);
            border: none;
            box-shadow: 0 4px 14px rgba(37,99,235,0.3), inset 0 1px 0 rgba(255,255,255,0.15);
            transition: all .25s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }

        body.es-role-office_user .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.2) 50%, transparent 100%);
            transition: left .5s ease;
        }

        body.es-role-office_user .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37,99,235,0.4), 0 0 0 2px rgba(37,99,235,0.08);
            background: linear-gradient(135deg, #1D4ED8 0%, #0284C7 100%);
        }

        body.es-role-office_user .btn-primary:hover::before {
            left: 100%;
        }

        body.es-role-office_user .btn-outline-primary {
            border: 1.5px solid rgba(37,99,235,0.35);
            color: #1D4ED8;
            background: rgba(219,234,254,0.25);
            transition: all .22s ease;
        }

        body.es-role-office_user .btn-outline-primary:hover {
            background: linear-gradient(135deg, #2563EB 0%, #0EA5E9 100%);
            border-color: transparent;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(37,99,235,0.28);
        }

        body.es-role-office_user .btn:hover {
            transform: translateY(-1px);
        }

        /* ── Enhanced tables ── */
        body.es-role-office_user .table th {
            background: rgba(248,250,252,0.55);
            backdrop-filter: blur(4px);
        }

        body.es-role-office_user .table-hover tbody tr {
            transition: all .2s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-office_user .table-hover tbody tr:hover {
            transform: scale(1.004);
            background: rgba(219,234,254,0.2) !important;
        }

        body.es-role-office_user .table-hover tbody tr:hover > td {
            background: transparent;
        }

        /* ── Staggered reveal ── */
        body.es-role-office_user .office-reveal,
        body.es-role-office_user [data-office-reveal] {
            opacity: 0;
            transform: translateY(14px) scale(0.98);
            transition: opacity .5s cubic-bezier(.4,0,.2,1), transform .5s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-office_user .office-reveal.is-visible,
        body.es-role-office_user [data-office-reveal].is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        body.es-role-office_user .office-reveal:nth-child(2) { transition-delay: .06s; }
        body.es-role-office_user .office-reveal:nth-child(3) { transition-delay: .12s; }
        body.es-role-office_user .office-reveal:nth-child(4) { transition-delay: .18s; }
        body.es-role-office_user .office-reveal:nth-child(5) { transition-delay: .24s; }

        /* ── Glass forms ── */
        body.es-role-office_user .form-control,
        body.es-role-office_user .form-select {
            background: rgba(255,255,255,0.9);
            border: 1px solid #94A3B8;
            backdrop-filter: blur(4px);
            transition: all .25s ease;
        }

        body.es-role-office_user .form-control:focus,
        body.es-role-office_user .form-select:focus {
            background: rgba(255,255,255,0.9);
            border-color: #2563EB;
            box-shadow: 0 0 0 4px rgba(37,99,235,0.1), 0 4px 12px rgba(37,99,235,0.06);
        }

        /* ── Scrollbar ── */
        body.es-role-office_user .es-sidebar::-webkit-scrollbar { width: 4px; }
        body.es-role-office_user .es-sidebar::-webkit-scrollbar-track { background: transparent; }
        body.es-role-office_user .es-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

        body.es-role-office_user .es-footer {
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(8px);
            border-top-color: rgba(226,232,240,0.45);
        }

        /* ═══════════════════════════════════════════════════════
           ADMIN PANEL — PREMIUM DESIGN SYSTEM
           Dark sidebar · Glass cards · Indigo accents
           ═══════════════════════════════════════════════════════ */

        body.es-role-admin .es-content {
            position: relative;
            background:
                radial-gradient(ellipse at 8% -8%, rgba(37,99,235,0.12) 0%, transparent 48%),
                radial-gradient(ellipse at 95% 105%, rgba(99,102,241,0.08) 0%, transparent 48%),
                var(--es-bg);
            overflow: hidden;
        }

        body.es-role-admin .es-content::before {
            content: '';
            position: fixed;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            top: -80px;
            left: -40px;
            background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, transparent 70%);
            animation: adminOrbit 28s ease-in-out infinite;
            pointer-events: none;
            z-index: 0;
            filter: blur(60px);
        }

        body.es-role-admin .es-content > *:not(.modal):not(.modal-backdrop) {
            position: relative;
            z-index: 1;
        }

        body.es-role-admin .es-content > .modal {
            z-index: 1060;
        }

        @keyframes adminOrbit {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(40px, 50px) scale(1.06); }
        }

        /* ── Dark sidebar ── */
        body.es-role-admin .es-sidebar {
            background: linear-gradient(180deg, #0D1321 0%, #141E30 50%, #101826 100%);
            border-right: 1px solid rgba(255,255,255,0.05);
            box-shadow: 4px 0 24px rgba(0,0,0,0.28), 1px 0 0 rgba(255,255,255,0.03);
        }

        body.es-role-admin .es-sidebar-brand {
            border-bottom: 1px solid rgba(255,255,255,0.05) !important;
        }

        body.es-role-admin .es-brand-name {
            color: #F1F5F9 !important;
        }

        body.es-role-admin .es-brand-sub {
            color: #64748B !important;
        }

        body.es-role-admin .es-nav-section {
            color: rgba(148,163,184,0.5);
            letter-spacing: .1em;
            font-size: .62rem;
            text-transform: uppercase;
            font-weight: 700;
        }

        body.es-role-admin .es-nav-link {
            border: 1px solid transparent;
            margin: .15rem .65rem;
            padding: .58rem .9rem;
            border-radius: .6rem;
            font-weight: 500;
            color: #94A3B8;
            transition: all .22s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-admin .es-nav-link i {
            color: #64748B;
            transition: color .22s ease;
        }

        body.es-role-admin .es-nav-link:hover {
            transform: translateX(3px);
            color: #E2E8F0;
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.07);
            box-shadow: 0 4px 12px rgba(0,0,0,0.18);
        }

        body.es-role-admin .es-nav-link:hover i {
            color: #818CF8;
        }

        body.es-role-admin .es-nav-link.active {
            color: #FFFFFF;
            border-color: rgba(99,102,241,0.25);
            background: linear-gradient(135deg, rgba(37,99,235,0.22) 0%, rgba(99,102,241,0.16) 100%);
            box-shadow: 0 8px 24px rgba(37,99,235,0.2), inset 0 1px 0 rgba(255,255,255,0.05);
        }

        body.es-role-admin .es-nav-link.active i {
            color: #818CF8;
        }

        body.es-role-admin .es-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            border-radius: 0 3px 3px 0;
            background: linear-gradient(180deg, #6366F1, #2563EB);
            box-shadow: 0 0 12px rgba(99,102,241,0.5);
        }

        body.es-role-admin .es-nav-badge {
            background: linear-gradient(135deg, #6366F1, #2563EB) !important;
            color: #fff !important;
            box-shadow: 0 2px 8px rgba(99,102,241,0.4);
        }

        body.es-role-admin .es-sidebar .es-nav-link[type="submit"],
        body.es-role-admin .es-sidebar button.es-nav-link {
            color: #94A3B8;
        }

        body.es-role-admin .es-sidebar .es-nav-link[type="submit"]:hover,
        body.es-role-admin .es-sidebar button.es-nav-link:hover {
            color: #FCA5A5;
            background: rgba(239,68,68,0.1);
            border-color: rgba(239,68,68,0.2);
        }

        /* ── Glass topbar ── */
        body.es-role-admin .es-topbar {
            border-bottom: 1px solid rgba(255,255,255,0.35);
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(18px) saturate(1.5);
            -webkit-backdrop-filter: blur(18px) saturate(1.5);
            box-shadow: 0 1px 3px rgba(15,23,42,0.04);
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-admin .es-topbar.is-scrolled {
            border-bottom-color: rgba(203,213,225,0.4);
            box-shadow: 0 8px 28px rgba(15,23,42,0.07);
            background: rgba(255,255,255,0.68);
        }

        body.es-role-admin .es-topbar-title {
            font-size: 1.04rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, #0F172A 0%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        body.es-role-admin .es-avatar {
            background: linear-gradient(135deg, #DBEAFE 0%, #E0E7FF 100%);
            border: 2px solid rgba(99,102,241,0.3);
            color: #4338CA;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.08), 0 8px 20px rgba(99,102,241,0.16);
            transition: all .3s ease;
        }

        body.es-role-admin .es-avatar:hover {
            box-shadow: 0 0 0 4px rgba(99,102,241,0.12), 0 12px 28px rgba(99,102,241,0.22);
            transform: scale(1.05);
        }

        /* ── Glass cards ── */
        body.es-role-admin .card {
            border-radius: .95rem;
            border: 1px solid rgba(255,255,255,0.45);
            background: rgba(255,255,255,0.65);
            backdrop-filter: blur(12px) saturate(1.3);
            -webkit-backdrop-filter: blur(12px) saturate(1.3);
            box-shadow: 0 4px 16px rgba(15,23,42,0.04), 0 1px 3px rgba(15,23,42,0.02);
            transition: all .3s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-admin .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(15,23,42,0.07), 0 6px 14px rgba(37,99,235,0.04);
            border-color: rgba(37,99,235,0.15);
        }

        body.es-role-admin .card-header {
            padding-top: 1.05rem;
            padding-bottom: 0.8rem;
            background: rgba(255,255,255,0.25);
            border-bottom: 1px solid rgba(226,232,240,0.55);
        }

        body.es-role-admin .card-title {
            color: #334155;
        }

        body.es-role-admin .table th {
            background: rgba(248,250,252,0.55);
            backdrop-filter: blur(4px);
            color: #8A96A8;
        }

        body.es-role-admin .table-hover tbody tr {
            transition: all .2s cubic-bezier(.4,0,.2,1);
        }

        body.es-role-admin .table-hover tbody tr:hover {
            transform: scale(1.003);
            background: rgba(219,234,254,0.18) !important;
        }

        body.es-role-admin .table-hover tbody tr:hover > td {
            background: transparent;
        }

        /* ── Gradient primary button ── */
        body.es-role-admin .btn-primary {
            background: linear-gradient(135deg, #4F46E5 0%, #2563EB 100%);
            border: none;
            box-shadow: 0 4px 14px rgba(79,70,229,0.3), inset 0 1px 0 rgba(255,255,255,0.12);
            transition: all .25s cubic-bezier(.4,0,.2,1);
            position: relative;
            overflow: hidden;
        }

        body.es-role-admin .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.18) 50%, transparent 100%);
            transition: left .5s ease;
        }

        body.es-role-admin .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(79,70,229,0.4), 0 0 0 2px rgba(79,70,229,0.08);
            background: linear-gradient(135deg, #4338CA 0%, #1D4ED8 100%);
        }

        body.es-role-admin .btn-primary:hover::before {
            left: 100%;
        }

        body.es-role-admin .btn-outline-primary {
            border: 1.5px solid rgba(79,70,229,0.35);
            color: #4338CA;
            background: rgba(238,242,255,0.25);
            transition: all .22s ease;
        }

        body.es-role-admin .btn-outline-primary:hover {
            background: linear-gradient(135deg, #4F46E5 0%, #2563EB 100%);
            border-color: transparent;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(79,70,229,0.28);
        }

        /* ── Glass forms ── */
        body.es-role-admin .form-control,
        body.es-role-admin .form-select {
            background: rgba(255,255,255,0.9);
            border: 1px solid #94A3B8;
            backdrop-filter: blur(4px);
            transition: all .25s ease;
        }

        body.es-role-admin .form-control:focus,
        body.es-role-admin .form-select:focus {
            background: rgba(255,255,255,0.9);
            border-color: #4F46E5;
            box-shadow: 0 0 0 4px rgba(79,70,229,0.1), 0 4px 12px rgba(79,70,229,0.06);
        }

        body.es-role-admin .breadcrumb-item a {
            color: #697A8D;
        }

        body.es-role-admin .breadcrumb-item.active {
            color: #334155;
        }

        /* ── Scrollbar ── */
        body.es-role-admin .es-sidebar::-webkit-scrollbar { width: 4px; }
        body.es-role-admin .es-sidebar::-webkit-scrollbar-track { background: transparent; }
        body.es-role-admin .es-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }

        body.es-role-admin .es-footer {
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(8px);
            border-top-color: rgba(226,232,240,0.45);
        }

        body.es-role-admin .admin-page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }

        body.es-role-admin .admin-page-title {
            font-weight: 700;
            margin: 0;
            font-size: 1rem;
            color: #566A7F;
        }

        body.es-role-admin .admin-page-sub {
            color: var(--es-muted);
            font-size: .78rem;
            margin: 0;
        }

        body.es-role-admin .admin-muted {
            color: var(--es-muted) !important;
        }

        body.es-role-admin .admin-stat-label {
            font-size: .76rem;
            color: var(--es-muted);
            font-weight: 500;
        }

        body.es-role-admin .admin-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #566A7F;
        }

        body.es-role-admin .admin-stat-sub {
            font-size: .72rem;
            color: var(--es-muted);
        }

        body.es-role-admin .admin-kpi-trend-wrap {
            margin-top: .4rem;
            display: flex;
            align-items: center;
            gap: .4rem;
            flex-wrap: wrap;
        }

        body.es-role-admin .admin-kpi-trend {
            display: inline-flex;
            align-items: center;
            gap: .22rem;
            padding: .14rem .45rem;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: .66rem;
            font-weight: 700;
            line-height: 1;
        }

        body.es-role-admin .admin-kpi-trend-up {
            color: var(--es-emerald);
            background: var(--es-emerald-s);
            border-color: #A7F3D0;
        }

        body.es-role-admin .admin-kpi-trend-down {
            color: var(--es-rose);
            background: var(--es-rose-s);
            border-color: #FECDD3;
        }

        body.es-role-admin .admin-kpi-trend-flat {
            color: var(--es-muted);
            background: #EEF2F7;
            border-color: var(--es-border-soft);
        }

        body.es-role-admin .admin-kpi-trend-label {
            color: var(--es-muted);
            font-size: .68rem;
            font-weight: 500;
        }

        body.es-role-admin .admin-plain-btn {
            background: #EEF2F7;
            border: 1px solid var(--es-border-soft);
            color: #566A7F;
        }

        body.es-role-admin .admin-plain-btn:hover {
            background: #E3E9F1;
            color: #44546A;
        }

        body.es-role-admin .admin-icon-btn {
            border: 1px solid var(--es-border-soft);
            background: #EEF2F7;
            color: #566A7F;
        }

        body.es-role-admin .admin-icon-btn:hover {
            background: #E3E9F1;
            color: #44546A;
        }

        body.es-role-admin .admin-trash-btn {
            background: #FEE2E2;
            border: 1px solid #FECACA;
            color: #DC2626;
        }

        body.es-role-admin .admin-trash-btn:hover {
            background: #FECACA;
            color: #B91C1C;
        }

        body.es-role-admin .admin-empty-state {
            padding: 2rem 1.25rem;
            text-align: center;
            color: var(--es-muted);
        }

        body.es-role-admin .admin-empty-state-icon {
            width: 2.7rem;
            height: 2.7rem;
            border-radius: .75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--es-primary-s);
            color: var(--es-primary);
            border: 1px solid var(--es-primary-m);
            margin-bottom: .65rem;
            font-size: 1.05rem;
        }

        body.es-role-admin .admin-empty-state-title {
            color: #566A7F;
            font-weight: 700;
            font-size: .84rem;
        }

        body.es-role-admin .admin-empty-state-copy {
            margin-top: .18rem;
            font-size: .76rem;
        }

        body.es-role-admin .admin-quick-modal .modal-dialog {
            max-width: 560px;
        }

        body.es-role-admin .admin-quick-modal .modal-content {
            border: 1px solid var(--es-border-soft);
            border-radius: .95rem;
            box-shadow: 0 22px 56px rgba(15, 23, 42, .14);
        }

        body.es-role-admin .admin-quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: .58rem;
        }

        body.es-role-admin .admin-quick-link {
            display: flex;
            align-items: center;
            gap: .56rem;
            border: 1px solid var(--es-border-soft);
            border-radius: .68rem;
            padding: .62rem .72rem;
            color: #475569;
            background: #F8FAFC;
            font-size: .79rem;
            font-weight: 600;
            transition: all .16s ease;
            text-decoration: none;
        }

        body.es-role-admin .admin-quick-link:hover {
            color: var(--es-primary);
            background: var(--es-primary-s);
            border-color: var(--es-primary-m);
        }

        body.es-role-admin .admin-quick-key {
            margin-left: auto;
            border: 1px solid var(--es-border-soft);
            background: #fff;
            color: #64748B;
            border-radius: .42rem;
            font-size: .62rem;
            font-weight: 700;
            padding: .12rem .34rem;
            line-height: 1;
        }

        body.es-role-admin .admin-table-toolbar {
            padding: .7rem 1rem;
            border-bottom: 1px solid var(--es-border-soft);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.8) 0%, rgba(248, 250, 252, 0.45) 100%);
        }

        body.es-role-admin .admin-table-toolbar-title {
            font-size: .82rem;
            font-weight: 600;
            color: #566A7F;
        }

        body.es-role-admin .admin-table-toolbar-sub {
            font-size: .74rem;
            color: var(--es-muted);
            margin-top: 1px;
        }

        body.es-role-admin .admin-table-wrap {
            max-height: 36rem;
            overflow: auto;
        }

        body.es-role-admin .admin-table-sticky thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #F8FAFC;
            box-shadow: inset 0 -1px 0 var(--es-border-soft);
        }

        body.es-role-admin .admin-table-interactive th[data-sort] {
            position: relative;
            cursor: pointer;
            user-select: none;
            padding-right: 1.45rem;
        }

        body.es-role-admin .admin-table-interactive th[data-sort]::after {
            content: '\F282';
            font-family: 'bootstrap-icons';
            font-size: .62rem;
            color: #94A3B8;
            position: absolute;
            right: .58rem;
            top: 50%;
            transform: translateY(-50%);
            opacity: .85;
            transition: color .16s ease, opacity .16s ease;
        }

        body.es-role-admin .admin-table-interactive th[data-sort].is-asc::after {
            content: '\F235';
            color: var(--es-primary);
            opacity: 1;
        }

        body.es-role-admin .admin-table-interactive th[data-sort].is-desc::after {
            content: '\F229';
            color: var(--es-primary);
            opacity: 1;
        }

        body.es-role-admin .admin-table-compact th {
            padding-top: .45rem !important;
            padding-bottom: .45rem !important;
            font-size: .66rem;
        }

        body.es-role-admin .admin-table-compact td {
            padding-top: .48rem !important;
            padding-bottom: .48rem !important;
        }

        body.es-role-admin .admin-density-switch {
            display: inline-flex;
            align-items: center;
            gap: .32rem;
        }

        body.es-role-admin .admin-density-btn {
            border: 1px solid var(--es-border-soft);
            border-radius: .48rem;
            background: #EEF2F7;
            color: #566A7F;
            font-size: .67rem;
            font-weight: 600;
            line-height: 1;
            padding: .32rem .46rem;
            transition: all .16s ease;
        }

        body.es-role-admin .admin-density-btn.is-active {
            background: var(--es-primary-s);
            color: var(--es-primary);
            border-color: var(--es-primary-m);
        }

        body.es-role-admin .admin-chip-filters {
            display: flex;
            align-items: center;
            gap: .4rem;
            flex-wrap: wrap;
            padding: .56rem .95rem;
            border-bottom: 1px solid var(--es-border-soft);
            background: #FCFDFE;
        }

        body.es-role-admin .admin-chip-filter {
            border: 1px solid var(--es-border-soft);
            background: #EEF2F7;
            color: #64748B;
            border-radius: 999px;
            font-size: .67rem;
            font-weight: 700;
            padding: .2rem .58rem;
            transition: all .16s ease;
        }

        body.es-role-admin .admin-chip-filter.is-active {
            background: var(--es-primary-s);
            color: var(--es-primary);
            border-color: var(--es-primary-m);
        }

        @keyframes adminBusyShimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        body.es-role-admin .admin-busy-target {
            position: relative;
        }

        body.es-role-admin .admin-busy-target.admin-is-busy {
            pointer-events: none;
        }

        body.es-role-admin .admin-busy-target.admin-is-busy::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .66);
            z-index: 15;
            backdrop-filter: blur(1px);
            -webkit-backdrop-filter: blur(1px);
        }

        body.es-role-admin .admin-busy-target.admin-is-busy::after {
            content: '';
            position: absolute;
            top: 1rem;
            left: 1rem;
            width: min(18rem, calc(100% - 2rem));
            height: .8rem;
            border-radius: .45rem;
            background: linear-gradient(90deg, rgba(148, 163, 184, .2) 0%, rgba(148, 163, 184, .42) 50%, rgba(148, 163, 184, .2) 100%);
            z-index: 16;
            animation: adminBusyShimmer 1.05s ease-in-out infinite;
        }

        body.es-role-admin .admin-reveal {
            opacity: 0;
            transform: translateY(10px);
            transition: opacity .34s ease, transform .34s ease;
        }

        body.es-role-admin .admin-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        body.es-role-admin .btn {
            transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease, border-color .18s ease;
        }

        body.es-role-admin .btn:hover {
            transform: translateY(-1px);
        }

        body.es-role-admin .card {
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        body.es-role-admin .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
            border-color: color-mix(in srgb, var(--es-primary) 20%, var(--es-border-soft) 80%);
        }

        body.es-role-admin .table-hover tbody tr {
            transition: transform .18s ease;
        }

        body.es-role-admin .table-hover tbody tr:hover {
            transform: translateX(2px);
        }

        body.es-role-admin .modal.fade .modal-dialog {
            transition: transform .22s ease, opacity .22s ease;
        }

        body.es-role-admin .modal.fade:not(.show) .modal-dialog {
            transform: translateY(12px) scale(.985);
        }

        @keyframes adminFadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        body.es-role-admin.admin-motion-ready .es-content > * {
            opacity: 0;
            animation: adminFadeUp .42s ease forwards;
            will-change: opacity, transform;
        }

        @media (prefers-reduced-motion: reduce) {
            body.es-role-admin .btn,
            body.es-role-admin .card,
            body.es-role-admin .table-hover tbody tr,
            body.es-role-admin .modal.fade .modal-dialog,
            body.es-role-admin .admin-reveal {
                transition: none !important;
            }

            body.es-role-admin.admin-motion-ready .es-content > * {
                opacity: 1;
                animation: none;
            }

            body.es-role-admin .admin-reveal {
                opacity: 1;
                transform: none;
            }

            body.es-role-citizen .btn,
            body.es-role-citizen .card,
            body.es-role-citizen .table-hover tbody tr,
            body.es-role-citizen .citizen-reveal,
            body.es-role-citizen [data-citizen-reveal] {
                transition: none !important;
            }

            body.es-role-citizen .citizen-reveal,
            body.es-role-citizen [data-citizen-reveal] {
                opacity: 1;
                transform: none;
            }

            body.es-role-office_user .btn,
            body.es-role-office_user .card,
            body.es-role-office_user .table-hover tbody tr,
            body.es-role-office_user .office-reveal,
            body.es-role-office_user [data-office-reveal] {
                transition: none !important;
            }

            body.es-role-office_user .office-reveal,
            body.es-role-office_user [data-office-reveal] {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 767.98px) {
            body.es-role-admin .admin-quick-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @yield('vendor-style')
    @yield('page-style')
    @vite(['resources/js/app.js'])
    @stack('styles')
</head>
@php
    $roleClass = auth()->check() ? 'es-role-' . auth()->user()->role : 'es-role-guest';
@endphp
<body class="{{ $roleClass }}">

@auth
@php
    $user = auth()->user();
    $baseHome = match ($user->role) {
        'admin'       => route('admin.dashboard'),
        'office_user' => route('office.dashboard'),
        default       => route('citizen.dashboard'),
    };
    $pendingOfficeRequests = $user->isOfficeUser()
        ? ($user->offices()->first()?->requests()->where('status', 'pending')->count() ?? 0)
        : 0;
    $activeCitizenRequests = $user->isCitizen()
        ? $user->serviceRequests()->whereNotIn('status', ['completed', 'rejected'])->count()
        : 0;
    $adminOpenTickets = $user->isAdmin()
        ? \App\Models\SupportTicket::where('status', 'open')->count()
        : 0;
    $citizenSupportUnread = $user->isCitizen()
        ? \App\Models\SupportTicketMessage::whereHas('ticket', fn($q) => $q->where('user_id', $user->id))
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count()
        : 0;
    $unreadCount = $user->unreadNotifications()->count();
@endphp

{{-- Sidebar overlay (mobile) --}}
<div class="es-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

{{-- Toast stack (global) --}}
<div id="toastStack" class="toast-stack" aria-live="polite" aria-atomic="true"></div>

<div class="es-wrapper">

    {{-- ── Sidebar ──────────────────────────────────────────── --}}
    <aside class="es-sidebar" id="esSidebar">

        {{-- Brand --}}
        <a href="{{ $baseHome }}" class="es-sidebar-brand">
            <span class="es-brand-mark">
                <img src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}" alt="CedarGov icon">
            </span>
            <span>
                <span class="es-brand-name d-block">CedarGov</span>
                <span class="es-brand-sub">Lebanon Gov Portal</span>
            </span>
        </a>

        {{-- Navigation --}}
        <nav class="es-nav">

        @if(session('success') || session('error') || session('info') || session('warning'))
        <div id="__flash"
            data-success="{{ session('success') }}"
            data-error="{{ session('error') }}"
            data-info="{{ session('info') }}"
            data-warning="{{ session('warning') }}"
            style="display:none"></div>
        @endif

            @if($user->isAdmin())
                <span class="es-nav-section">Administration</span>

                <a href="{{ route('admin.dashboard') }}"
                   class="es-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span class="es-nav-label">Dashboard</span>
                </a>
                <a href="{{ route('admin.municipalities') }}"
                   class="es-nav-link {{ request()->routeIs('admin.municipalities*') ? 'active' : '' }}">
                    <i class="bi bi-map"></i>
                    <span class="es-nav-label">Municipalities</span>
                </a>
                <a href="{{ route('admin.offices') }}"
                   class="es-nav-link {{ request()->routeIs('admin.offices*') ? 'active' : '' }}">
                    <i class="bi bi-buildings"></i>
                    <span class="es-nav-label">Offices</span>
                </a>
                <a href="{{ route('admin.users') }}"
                   class="es-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span class="es-nav-label">Users</span>
                </a>
                <a href="{{ route('admin.reports') }}"
                   class="es-nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i>
                    <span class="es-nav-label">Reports</span>
                </a>
                <a href="{{ route('admin.support') }}"
                   class="es-nav-link {{ request()->routeIs('admin.support*') ? 'active' : '' }}">
                    <i class="bi bi-life-preserver"></i>
                    <span class="es-nav-label">Support</span>
                    @if($adminOpenTickets > 0)
                        <span class="es-nav-badge">{{ $adminOpenTickets }}</span>
                    @endif
                </a>
                <a href="{{ Route::has('admin.settings') ? route('admin.settings') : route('security.2fa') }}"
                   class="es-nav-link {{ request()->routeIs('admin.settings') || request()->routeIs('security.2fa') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i>
                    <span class="es-nav-label">Settings</span>
                </a>

            @elseif($user->isOfficeUser())
                <span class="es-nav-section">Office Panel</span>

                <a href="{{ route('office.dashboard') }}"
                   class="es-nav-link {{ request()->routeIs('office.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span class="es-nav-label">Dashboard</span>
                </a>
                <a href="{{ route('office.services') }}"
                   class="es-nav-link {{ request()->routeIs('office.services*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span class="es-nav-label">Services</span>
                </a>
                <a href="{{ route('office.requests') }}"
                   class="es-nav-link {{ request()->routeIs('office.requests*') ? 'active' : '' }}">
                    <i class="bi bi-inbox"></i>
                    <span class="es-nav-label">Requests</span>
                    @if($pendingOfficeRequests > 0)
                        <span class="es-nav-badge">{{ $pendingOfficeRequests }}</span>
                    @endif
                </a>
                <a href="{{ route('office.appointments') }}"
                   class="es-nav-link {{ request()->routeIs('office.appointments*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i>
                    <span class="es-nav-label">Appointments</span>
                </a>
                <a href="{{ route('office.feedback') }}"
                   class="es-nav-link {{ request()->routeIs('office.feedback*') ? 'active' : '' }}">
                    <i class="bi bi-chat-left-text"></i>
                    <span class="es-nav-label">Feedback</span>
                </a>
                <a href="{{ route('office.profile') }}"
                   class="es-nav-link {{ request()->routeIs('office.profile*') ? 'active' : '' }}">
                    <i class="bi bi-id-card"></i>
                    <span class="es-nav-label">Profile</span>
                </a>

            @else
                <span class="es-nav-section">Citizen Portal</span>

                <a href="{{ route('citizen.dashboard') }}"
                   class="es-nav-link {{ request()->routeIs('citizen.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i>
                    <span class="es-nav-label">Dashboard</span>
                </a>
                <a href="{{ route('citizen.offices') }}"
                   class="es-nav-link {{ request()->routeIs('citizen.offices*') || request()->routeIs('citizen.services*') ? 'active' : '' }}">
                    <i class="bi bi-search"></i>
                    <span class="es-nav-label">Browse Services</span>
                </a>
                <a href="{{ route('citizen.requests') }}"
                   class="es-nav-link {{ request()->routeIs('citizen.requests*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text"></i>
                    <span class="es-nav-label">My Requests</span>
                    @if($activeCitizenRequests > 0)
                        <span class="es-nav-badge">{{ $activeCitizenRequests }}</span>
                    @endif
                </a>
                <a href="{{ route('citizen.appointments') }}"
                   class="es-nav-link {{ request()->routeIs('citizen.appointments') ? 'active' : '' }}">
                    <i class="bi bi-calendar-event"></i>
                    <span class="es-nav-label">Appointments</span>
                </a>
                <a href="{{ route('citizen.payments') }}"
                   class="es-nav-link {{ request()->routeIs('citizen.payments') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i>
                    <span class="es-nav-label">Payments</span>
                </a>
                <a href="{{ route('citizen.profile') }}"
                   class="es-nav-link {{ request()->routeIs('citizen.profile*') ? 'active' : '' }}">
                    <i class="bi bi-person"></i>
                    <span class="es-nav-label">Profile</span>
                </a>
                <a href="{{ route('citizen.support') }}"
                   class="es-nav-link {{ request()->routeIs('citizen.support*') ? 'active' : '' }}">
                    <i class="bi bi-life-preserver"></i>
                    <span class="es-nav-label">Support</span>
                    @if($citizenSupportUnread > 0)
                        <span class="es-nav-badge">{{ $citizenSupportUnread }}</span>
                    @endif
                </a>
            @endif

            {{-- Shared bottom links --}}
            <span class="es-nav-section" style="margin-top:1.5rem;">Account</span>
            <a href="{{ route('security.2fa') }}"
               class="es-nav-link {{ request()->routeIs('security.2fa') ? 'active' : '' }}">
                <i class="bi bi-shield-check"></i>
                <span class="es-nav-label">Security</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="es-nav-link w-100 text-start border-0 bg-transparent"
                        style="cursor:pointer; color:var(--es-muted);">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="es-nav-label">Log Out</span>
                </button>
            </form>

        </nav>
    </aside>
    {{-- ── / Sidebar ─────────────────────────────────────────── --}}

    {{-- ── Main ────────────────────────────────────────────────── --}}
    <div class="es-main">

        {{-- Topbar --}}
        <header class="es-topbar">
            <button class="es-topbar-toggle" onclick="toggleSidebar()" aria-label="Toggle menu">
                <i class="bi bi-list"></i>
            </button>

            <h1 class="es-topbar-title">@yield('page-title', 'Dashboard')</h1>

            <div class="es-topbar-right">

                @if($user->isAdmin())
                <button
                    class="es-topbar-btn"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#adminQuickActionsModal"
                    aria-label="Quick actions"
                    title="Quick actions (Ctrl/Cmd + K)">
                    <i class="bi bi-lightning-charge"></i>
                </button>
                @endif

                {{-- Notifications --}}
                <div class="dropdown">
                    <button id="notificationBell" class="es-topbar-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        @if($unreadCount > 0)
                            <span class="es-notif-dot"></span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="width:310px; max-height:380px; overflow-y:auto; padding:.5rem 0;">
                        <li>
                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="border-color:var(--es-border)!important;">
                                <span style="font-size:.82rem; font-weight:700; color:var(--es-text);">Notifications</span>
                                @if($unreadCount > 0)
                                    <span class="badge rounded-pill" style="background:var(--es-primary-s); color:var(--es-primary); font-size:.65rem;">{{ $unreadCount }} new</span>
                                @endif
                            </div>
                        </li>
                        @forelse($user->unreadNotifications->take(6) as $notification)
                            <li>
                                <div class="dropdown-item py-2" style="border-radius:0;">
                                    <div style="font-size:.82rem; font-weight:600; color:var(--es-text); line-height:1.4;">
                                        {{ $notification->data['message'] ?? 'New notification' }}
                                    </div>
                                    <div style="font-size:.72rem; color:var(--es-muted); margin-top:.2rem;">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li>
                                <div class="text-center py-4" style="color:var(--es-muted); font-size:.84rem;">
                                    <i class="bi bi-bell-slash d-block mb-1" style="font-size:1.5rem; opacity:.4;"></i>
                                    No new notifications
                                </div>
                            </li>
                        @endforelse
                    </ul>
                </div>

                {{-- User menu --}}
                <div class="dropdown ms-1">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="es-avatar" data-bs-toggle="dropdown" role="button" aria-expanded="false" style="object-fit:cover;" referrerpolicy="no-referrer">
                    @else
                        <div class="es-avatar" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width:210px;">
                        <li>
                            <div class="px-3 py-2 border-bottom" style="border-color:var(--es-border)!important;">
                                <div style="font-size:.875rem; font-weight:700; color:var(--es-text);">{{ $user->name }}</div>
                                <div style="font-size:.72rem; color:var(--es-muted);">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</div>
                            </div>
                        </li>
                        @if($user->isCitizen())
                            <li><a class="dropdown-item mt-1" href="{{ route('citizen.profile') }}">
                                <i class="bi bi-person me-2" style="color:var(--es-muted);"></i>Profile
                            </a></li>
                        @elseif($user->isOfficeUser())
                            <li><a class="dropdown-item mt-1" href="{{ route('office.profile') }}">
                                <i class="bi bi-id-card me-2" style="color:var(--es-muted);"></i>Profile
                            </a></li>
                        @endif
                        <li><a class="dropdown-item" href="{{ route('security.2fa') }}">
                            <i class="bi bi-shield-check me-2" style="color:var(--es-muted);"></i>Security
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i>Log Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            </div>
        </header>
        {{-- / Topbar --}}

        {{-- Content --}}
        <main class="es-content">

            {{-- Breadcrumb --}}
            @php $segments = request()->segments(); @endphp
            @if(count($segments) > 1)
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ $baseHome }}">Home</a></li>
                    @foreach($segments as $i => $segment)
                        @php
                            $label = ucfirst(str_replace('-', ' ', $segment));
                            $isLast = ($i === count($segments) - 1);
                        @endphp
                        @if($isLast)
                            <li class="breadcrumb-item active">{{ $label }}</li>
                        @else
                            <li class="breadcrumb-item">{{ $label }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
            @endif

            {{-- Validation errors render inline; success/error/info/warning render as toasts --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0 ps-3 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Page content --}}
            @yield('content')

        </main>

        {{-- Footer --}}
        <footer class="es-footer">
            &copy; {{ now()->year }} CedarGov Platform &mdash; Lebanese Municipalities
        </footer>

    </div>
    {{-- ── / Main ───────────────────────────────────────────────── --}}

</div>

@if($user->isAdmin())
<div class="modal fade admin-quick-modal" id="adminQuickActionsModal" tabindex="-1" aria-labelledby="adminQuickActionsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border:none;padding:1.05rem 1.2rem .35rem;">
                <h6 class="modal-title" id="adminQuickActionsTitle" style="font-weight:700;color:#566A7F;">
                    Quick Actions
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:.7rem 1.2rem 1.1rem;">
                <div class="admin-quick-grid">
                    <a class="admin-quick-link" href="{{ route('admin.users', ['quick' => 'add']) }}">
                        <i class="bi bi-person-plus"></i> Create Office User
                        <span class="admin-quick-key">Users</span>
                    </a>
                    <a class="admin-quick-link" href="{{ route('admin.offices', ['quick' => 'add']) }}">
                        <i class="bi bi-building-add"></i> Add Office
                        <span class="admin-quick-key">Offices</span>
                    </a>
                    <a class="admin-quick-link" href="{{ route('admin.municipalities', ['quick' => 'add']) }}">
                        <i class="bi bi-pin-map"></i> Add Municipality
                        <span class="admin-quick-key">Municipalities</span>
                    </a>
                    <a class="admin-quick-link" href="{{ route('admin.reports') }}">
                        <i class="bi bi-graph-up-arrow"></i> Open Reports
                        <span class="admin-quick-key">Analytics</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if(auth()->check() && auth()->user()->isCitizen() && filled(config('services.groq.api_key')))
    @include('partials.chatbot-widget')
@endif

@endauth

@guest
    @yield('content')
@endguest

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Sidebar toggle --}}
<script>
const __mqMobile = window.matchMedia('(max-width: 991.98px)');

function toggleSidebar() {
    const open = document.getElementById('esSidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('open', open);
    document.body.classList.toggle('es-sidebar-open', open);
}
function closeSidebar() {
    document.getElementById('esSidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
    document.body.classList.remove('es-sidebar-open');
}
__mqMobile.addEventListener('change', e => { if (!e.matches) closeSidebar(); });

/* ── Global UX: toasts, flash messages, sidebar swipe (runs for all roles) ── */
(function () {
    /* Toasts */
    const ts = document.getElementById('toastStack');
    const icons = {
        success: 'bi-check-circle-fill',
        error:   'bi-x-circle-fill',
        info:    'bi-info-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
    };
    window.showToast = function (msg, type, dur) {
        if (!msg || !ts) return;
        type = type || 'info';
        dur = dur || 4500;
        const t = document.createElement('div');
        t.className = `toast-item ${type}`;
        const icon = document.createElement('i');
        icon.className = `bi ${icons[type] || icons.info}`;
        icon.style.cssText = 'font-size:1rem;flex-shrink:0;line-height:1.45';
        const span = document.createElement('span');
        span.style.flex = '1';
        span.textContent = msg;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Dismiss');
        btn.style.cssText = 'background:none;border:none;color:rgba(255,255,255,.55);cursor:pointer;font-size:1.1rem;padding:0;line-height:1';
        btn.innerHTML = '&times;';
        btn.addEventListener('click', () => window.closeToast(t));
        t.appendChild(icon); t.appendChild(span); t.appendChild(btn);
        ts.appendChild(t);
        setTimeout(() => window.closeToast(t), dur);
    };
    window.closeToast = function (el) {
        if (!el || !el.parentElement) return;
        el.classList.add('out');
        el.addEventListener('animationend', () => el.remove(), { once: true });
    };

    /* Flash messages → toasts */
    const fd = document.getElementById('__flash');
    if (fd) {
        if (fd.dataset.success) setTimeout(() => showToast(fd.dataset.success, 'success'), 200);
        if (fd.dataset.error)   setTimeout(() => showToast(fd.dataset.error,   'error'),   200);
        if (fd.dataset.info)    setTimeout(() => showToast(fd.dataset.info,    'info'),    200);
        if (fd.dataset.warning) setTimeout(() => showToast(fd.dataset.warning, 'warning'), 200);
    }

    /* Sidebar swipe — mobile only */
    const sb = document.getElementById('esSidebar');
    if (sb) {
        let tx = 0, ty = 0, drag = false;
        document.addEventListener('touchstart', e => {
            if (!__mqMobile.matches) return;
            tx = e.touches[0].clientX; ty = e.touches[0].clientY; drag = false;
        }, { passive: true });
        document.addEventListener('touchmove', e => {
            if (!__mqMobile.matches) return;
            if (Math.abs(e.touches[0].clientX - tx) > Math.abs(e.touches[0].clientY - ty)) drag = true;
        }, { passive: true });
        document.addEventListener('touchend', e => {
            if (!__mqMobile.matches || !drag) return;
            const dx = e.changedTouches[0].clientX - tx;
            if (tx < 28 && dx > 65) toggleSidebar();
            else if (dx < -55 && sb.classList.contains('open')) closeSidebar();
        }, { passive: true });
    }

    /* Escape closes mobile sidebar */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && document.body.classList.contains('es-sidebar-open')) closeSidebar();
    });
})();

(function () {
    const body = document.body;
    if (!body.classList.contains('es-role-admin')) return;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const content = document.querySelector('.es-content');
    if (content && !prefersReducedMotion) {
        body.classList.add('admin-motion-ready');
        const revealItems = Array.from(content.children);
        revealItems.forEach((el, index) => {
            el.style.animationDelay = `${Math.min(index * 45, 220)}ms`;
        });
    }

    @auth
        function addRealtimeNotification(message, url = '#') {
            const bellBtn = document.getElementById('notificationBell');
            const dropdownMenu = bellBtn?.nextElementSibling;
            const unreadBadge = bellBtn?.querySelector('.top-dot');
            const headerCount = dropdownMenu?.querySelector('.dropdown-header span');

            if (!unreadBadge && bellBtn) {
                const badge = document.createElement('span');
                badge.className = 'top-dot';
                badge.textContent = '1';
                bellBtn.appendChild(badge);
            } else if (unreadBadge) {
                const current = parseInt(unreadBadge.textContent || '0', 10);
                unreadBadge.textContent = String(Math.min(current + 1, 9));
            }

            if (headerCount) {
                const currentHeader = parseInt(headerCount.textContent || '0', 10) || 0;
                headerCount.textContent = `${currentHeader + 1} new`;
            }

            const emptyState = dropdownMenu?.querySelector('.bi-bell-slash')?.closest('div');
            if (emptyState) {
                emptyState.remove();
            }

            const newItem = document.createElement('a');
            newItem.className = 'dropdown-item';
            newItem.href = url;
            newItem.style.whiteSpace = 'normal';
            newItem.innerHTML = `
                <div style="display:flex;gap:.45rem">
                    <span style="width:7px;height:7px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:5px"></span>
                    <div>
                        <div style="font-size:.78rem;line-height:1.45;color:var(--ink-600)">${message}</div>
                        <div style="font-size:.65rem;color:var(--ink-400);margin-top:1px">Just now</div>
                    </div>
                </div>
            `;

            const header = dropdownMenu?.querySelector('.dropdown-header');
            if (header && header.nextSibling) {
                dropdownMenu.insertBefore(newItem, header.nextSibling);
            } else if (dropdownMenu) {
                dropdownMenu.appendChild(newItem);
            }

            showToast(message, 'info');
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Echo) {
                console.error('Echo is not loaded');
                return;
            }

            window.Echo.private('user.{{ auth()->id() }}')
                .listen('.request.status.updated', (e) => {
                    const statusText = String(e.new_status || '').replaceAll('_', ' ');
                    const message = `Your request #${e.reference_number} status changed to ${statusText}.`;
                    const url = `{{ auth()->user()->isOfficeUser() ? url('/office/requests') : url('/citizen/requests') }}/${e.request_id}`;
                    addRealtimeNotification(message, url);
                })
                .listen('.appointment.reminder', (e) => {
                    const url = e.service_request_id
                        ? `{{ auth()->user()->isOfficeUser() ? url('/office/requests') : url('/citizen/requests') }}/${e.service_request_id}`
                        : `{{ auth()->user()->isOfficeUser() ? url('/office/dashboard') : url('/citizen/offices') }}/${e.office_id}`;

                    addRealtimeNotification(e.message || 'Appointment reminder', url);
                })
                .listen('.message.sent', (e) => {
                    if (e.sender_id === {{ auth()->id() }}) {
                        return;
                    }

                    const url = `{{ auth()->user()->isOfficeUser() ? url('/office/requests') : url('/citizen/requests') }}/${e.service_request_id}`;
                    const senderName = e.sender?.name || 'Someone';
                    addRealtimeNotification(`New message from ${senderName}.`, url);
                });

            @if(auth()->user()->isOfficeUser() && auth()->user()->offices()->first())
            window.Echo.private('office.{{ auth()->user()->offices()->first()->id }}')
                .listen('.message.sent', (e) => {
                    if (e.sender_id === {{ auth()->id() }}) {
                        return;
                    }

                    const url = `{{ url('/office/requests') }}/${e.service_request_id}`;
                    const senderName = e.sender?.name || 'Citizen';
                    addRealtimeNotification(`New message from ${senderName}.`, url);
                });
            @endif

            const bellBtn = document.getElementById('notificationBell');

            bellBtn?.addEventListener('shown.bs.dropdown', async () => {
                try {
                    await fetch('{{ route('notifications.readAll') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json',
                        }
                    });

                    bellBtn.querySelector('.top-dot')?.remove();

                    const dropdownMenu = bellBtn.nextElementSibling;
                    const headerCount = dropdownMenu?.querySelector('.dropdown-header span');
                    if (headerCount) {
                        headerCount.remove();
                    }
                } catch (error) {
                    console.error('Failed to mark notifications as read');
                }
            });
        });
    @endauth

    /* Session expiry countdown + history lock for authenticated pages */
    @auth
    const ROLE_HOME_URL='{{ match(auth()->user()->role){ "admin" => route("admin.dashboard"), "office_user" => route("office.dashboard"), default => route("citizen.dashboard") } }}';

    // Prevent browser back/forward from traversing authenticated history.
    if(window.history && window.history.pushState){
        window.history.pushState({locked:true},'',window.location.href);
        window.addEventListener('popstate',()=>{
            window.history.pushState({locked:true},'',window.location.href);
            if(window.location.href!==ROLE_HOME_URL){
                window.location.replace(ROLE_HOME_URL);
            }
        });
    }
    @endauth

    const parseCellValue = (text, type) => {
        const raw = String(text ?? '').trim();
        if (type === 'number') {
            const parsed = Number(raw.replace(/[^0-9.-]+/g, ''));
            return Number.isFinite(parsed) ? parsed : Number.NEGATIVE_INFINITY;
        }
        if (type === 'date') {
            const parsed = Date.parse(raw);
            return Number.isFinite(parsed) ? parsed : Number.NEGATIVE_INFINITY;
        }
        return raw.toLowerCase();
    };

    const tableStoragePrefix = 'admin_table_state_v1_';
    const safeReadStorage = (key) => {
        try {
            const raw = window.localStorage.getItem(key);
            return raw ? JSON.parse(raw) : {};
        } catch {
            return {};
        }
    };
    const safeWriteStorage = (key, value) => {
        try {
            window.localStorage.setItem(key, JSON.stringify(value));
        } catch {
            // Ignore storage failures.
        }
    };
    const getTableKey = (table, selectorHint = null) => {
        if (!table) return null;
        if (table.dataset.adminTableKey) return table.dataset.adminTableKey;
        if (table.id) return table.id;
        if (selectorHint) return selectorHint;
        return `table-${Array.from(document.querySelectorAll('table[data-admin-table]')).indexOf(table)}`;
    };
    const readTableState = (tableKey) => safeReadStorage(`${tableStoragePrefix}${tableKey}`);
    const writeTableState = (tableKey, patch) => {
        const current = readTableState(tableKey);
        safeWriteStorage(`${tableStoragePrefix}${tableKey}`, { ...current, ...patch });
    };

    const sortTableByColumn = (table, columnIndex, sortType, direction) => {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const aCell = a.children[columnIndex];
            const bCell = b.children[columnIndex];
            const aRaw = aCell ? (aCell.dataset.sortValue ?? aCell.innerText) : '';
            const bRaw = bCell ? (bCell.dataset.sortValue ?? bCell.innerText) : '';
            const aValue = parseCellValue(aRaw, sortType);
            const bValue = parseCellValue(bRaw, sortType);

            if (aValue < bValue) return direction === 'asc' ? -1 : 1;
            if (aValue > bValue) return direction === 'asc' ? 1 : -1;
            return 0;
        });

        rows.forEach((row) => tbody.appendChild(row));
    };

    const initSortableTables = (root) => {
        root.querySelectorAll('table[data-admin-table]').forEach((table) => {
            if (table.dataset.sortBound === '1') return;
            table.dataset.sortBound = '1';

            const tableKey = getTableKey(table);
            const savedState = tableKey ? readTableState(tableKey) : {};
            const headers = Array.from(table.querySelectorAll('thead th[data-sort]'));
            const applySort = (header, direction) => {
                if (!header) return;
                const columnIndex = Number(header.dataset.sort);
                const sortType = header.dataset.sortType || 'text';

                headers.forEach((th) => th.classList.remove('is-asc', 'is-desc'));
                header.classList.add(direction === 'asc' ? 'is-asc' : 'is-desc');

                table.dataset.sortIndex = String(columnIndex);
                table.dataset.sortDir = direction;
                sortTableByColumn(table, columnIndex, sortType, direction);

                if (tableKey) {
                    writeTableState(tableKey, { sortIndex: columnIndex, sortDir: direction });
                }
            };

            headers.forEach((header) => {
                header.addEventListener('click', () => {
                    const isCurrent = table.dataset.sortIndex === header.dataset.sort;
                    const nextDirection = isCurrent && table.dataset.sortDir === 'asc' ? 'desc' : 'asc';
                    applySort(header, nextDirection);
                });
            });

            const savedHeader = headers.find((th) => Number(th.dataset.sort) === Number(savedState.sortIndex));
            if (savedHeader && (savedState.sortDir === 'asc' || savedState.sortDir === 'desc')) {
                applySort(savedHeader, savedState.sortDir);
            }
        });
    };

    const initDensitySwitches = (root) => {
        root.querySelectorAll('.admin-density-switch').forEach((switchWrap) => {
            const buttons = Array.from(switchWrap.querySelectorAll('[data-admin-density-target]'));
            if (!buttons.length) return;

            const getTable = () => {
                const selector = buttons[0].dataset.adminDensityTarget;
                return selector ? document.querySelector(selector) : null;
            };
            const table = getTable();
            const tableKey = getTableKey(table, buttons[0].dataset.adminDensityTarget || null);
            const savedState = tableKey ? readTableState(tableKey) : {};

            const applyDensity = (density) => {
                const targetTable = getTable();
                if (!targetTable) return;
                targetTable.classList.toggle('admin-table-compact', density === 'compact');
                buttons.forEach((btn) => {
                    btn.classList.toggle('is-active', (btn.dataset.adminDensity || 'comfortable') === density);
                });
                if (tableKey) {
                    writeTableState(tableKey, { density });
                }
            };

            buttons.forEach((button) => {
                if (button.dataset.densityBound === '1') return;
                button.dataset.densityBound = '1';

                button.addEventListener('click', () => {
                    applyDensity(button.dataset.adminDensity || 'comfortable');
                });
            });

            const initialDensity = (savedState.density === 'compact' || savedState.density === 'comfortable')
                ? savedState.density
                : (buttons.find((btn) => btn.classList.contains('is-active'))?.dataset.adminDensity || 'comfortable');
            applyDensity(initialDensity);
        });
    };

    const initChipFilters = (root) => {
        root.querySelectorAll('[data-admin-filter-group]').forEach((group) => {
            const buttons = Array.from(group.querySelectorAll('[data-admin-table-filter]'));
            if (!buttons.length) return;

            const selector = buttons[0].dataset.adminTableFilterTarget;
            const field = buttons[0].dataset.adminFilterField;
            if (!selector || !field) return;

            const table = document.querySelector(selector);
            const tbody = table?.querySelector('tbody');
            if (!tbody) return;

            const tableKey = getTableKey(table, selector);
            const savedState = tableKey ? readTableState(tableKey) : {};
            let currentFilters = savedState.filters || {};

            const applyFilter = (button) => {
                const value = (button.dataset.adminFilterValue || 'all').toLowerCase();
                tbody.querySelectorAll('tr').forEach((row) => {
                    const rowValue = String(row.dataset[field] || '').toLowerCase();
                    row.style.display = value === 'all' || rowValue === value ? '' : 'none';
                });

                buttons.forEach((btn) => {
                    btn.classList.toggle('is-active', btn === button);
                });

                if (tableKey) {
                    const nextFilters = { ...currentFilters, [field]: value };
                    currentFilters = nextFilters;
                    writeTableState(tableKey, { filters: nextFilters });
                }
            };

            buttons.forEach((button) => {
                if (button.dataset.filterBound === '1') return;
                button.dataset.filterBound = '1';
                button.addEventListener('click', () => applyFilter(button));
            });

            const initialValue = typeof currentFilters[field] === 'string' ? currentFilters[field] : null;
            const initialButton = buttons.find((btn) => (btn.dataset.adminFilterValue || 'all').toLowerCase() === initialValue)
                || buttons.find((btn) => btn.classList.contains('is-active'))
                || buttons[0];
            applyFilter(initialButton);
        });
    };

    const initBusyTargets = (root) => {
        root.querySelectorAll('form[data-admin-busy-target]').forEach((form) => {
            if (form.dataset.busyBound === '1') return;
            form.dataset.busyBound = '1';

            form.addEventListener('submit', (event) => {
                if (event.defaultPrevented) return;
                const selector = form.dataset.adminBusyTarget;
                if (!selector) return;
                const target = document.querySelector(selector);
                target?.classList.add('admin-is-busy');
            });
        });

        root.querySelectorAll('a[data-admin-busy-target]').forEach((link) => {
            if (link.dataset.busyBound === '1') return;
            link.dataset.busyBound = '1';

            link.addEventListener('click', (event) => {
                if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                const selector = link.dataset.adminBusyTarget;
                if (!selector) return;
                const target = document.querySelector(selector);
                target?.classList.add('admin-is-busy');
            });
        });

        root.querySelectorAll('.admin-busy-target .pagination a').forEach((link) => {
            if (link.dataset.paginationBusyBound === '1') return;
            link.dataset.paginationBusyBound = '1';

            link.addEventListener('click', (event) => {
                if (event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
                const target = link.closest('.admin-busy-target');
                target?.classList.add('admin-is-busy');
            });
        });
    };

    const initDirtyModalForms = (root) => {
        root.querySelectorAll('.modal form').forEach((form) => {
            if (form.dataset.dirtyBound === '1') return;
            form.dataset.dirtyBound = '1';

            const modalEl = form.closest('.modal');
            let snapshot = '';
            let submitting = false;

            const makeSnapshot = () => new URLSearchParams(new FormData(form)).toString();
            const refreshDirtyState = () => {
                if (submitting) return;
                form.dataset.formDirty = makeSnapshot() !== snapshot ? '1' : '0';
            };
            const resetState = () => {
                submitting = false;
                snapshot = makeSnapshot();
                form.dataset.formDirty = '0';
            };

            form.addEventListener('input', refreshDirtyState);
            form.addEventListener('change', refreshDirtyState);
            form.addEventListener('submit', () => {
                submitting = true;
                form.dataset.formDirty = '0';
            });

            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', () => {
                    resetState();
                });
                modalEl.addEventListener('hidden.bs.modal', () => {
                    resetState();
                });
                modalEl.addEventListener('hide.bs.modal', (event) => {
                    if (submitting || form.dataset.formDirty !== '1') return;
                    const shouldDiscard = window.confirm('Discard unsaved changes?');
                    if (!shouldDiscard) {
                        event.preventDefault();
                        event.stopImmediatePropagation();
                    }
                });
            } else {
                resetState();
            }
        });

        if (!window.__adminDirtyBeforeUnloadBound) {
            window.__adminDirtyBeforeUnloadBound = true;
            window.addEventListener('beforeunload', (event) => {
                const hasDirtyForm = document.querySelector('.modal form[data-form-dirty="1"]');
                if (!hasDirtyForm) return;
                event.preventDefault();
                event.returnValue = '';
            });
        }
    };

    const revealObserver = !prefersReducedMotion && 'IntersectionObserver' in window
        ? new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12 })
        : null;

    const initReveals = (root) => {
        root.querySelectorAll('.admin-reveal').forEach((el, index) => {
            if (el.dataset.revealBound === '1') return;
            el.dataset.revealBound = '1';
            el.style.transitionDelay = `${Math.min(index * 60, 360)}ms`;

            if (prefersReducedMotion || !revealObserver) {
                el.classList.add('is-visible');
            } else {
                revealObserver.observe(el);
            }
        });
    };

    /* ── Admin topbar scroll shadow ── */
    const initAdminTopbar = () => {
        const topbar = document.querySelector('.es-topbar');
        if (!topbar || topbar.dataset.adminScrollBound === '1') return;
        topbar.dataset.adminScrollBound = '1';
        let ticking = false;
        const sync = () => { topbar.classList.toggle('is-scrolled', window.scrollY > 6); ticking = false; };
        sync();
        window.addEventListener('scroll', () => {
            if (!ticking) { ticking = true; requestAnimationFrame(sync); }
        }, { passive: true });
    };

    /* ── Admin KPI card tilt ── */
    const initAdminTilt = (root) => {
        if (prefersReducedMotion) return;
        root.querySelectorAll('.admin-kpi-card').forEach((card) => {
            if (card.dataset.adminTiltBound === '1') return;
            card.dataset.adminTiltBound = '1';
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = `perspective(600px) rotateY(${x * 4}deg) rotateX(${-y * 4}deg) translateY(-3px)`;
            });
            card.addEventListener('mouseleave', () => { card.style.transform = ''; });
        });
    };

    window.initAdminGlobalUX = function initAdminGlobalUX(root = document) {
        initSortableTables(root);
        initDensitySwitches(root);
        initChipFilters(root);
        initBusyTargets(root);
        initDirtyModalForms(root);
        initReveals(root);
        initAdminTopbar();
        initAdminTilt(root);
    };

    window.initAdminGlobalUX(document);

    const openQuickActions = () => {
        const modalEl = document.getElementById('adminQuickActionsModal');
        if (!modalEl || typeof bootstrap === 'undefined') return;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    };

    document.addEventListener('keydown', (event) => {
        if (!body.classList.contains('es-role-admin')) return;
        if (!(event.ctrlKey || event.metaKey)) return;
        if (event.shiftKey || event.altKey) return;
        if (event.key.toLowerCase() !== 'k') return;
        event.preventDefault();
        openQuickActions();
    });
})();

(function () {
    const body = document.body;
    if (!body.classList.contains('es-role-citizen')) return;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ── Topbar scroll state ── */
    const topbar = document.querySelector('.es-topbar');
    if (topbar) {
        let ticking = false;
        const syncTopbarState = () => {
            topbar.classList.toggle('is-scrolled', window.scrollY > 6);
            ticking = false;
        };
        syncTopbarState();
        window.addEventListener('scroll', () => {
            if (!ticking) { ticking = true; requestAnimationFrame(syncTopbarState); }
        }, { passive: true });
    }

    /* ── Smooth elastic counter animation ── */
    const animateCounter = (el) => {
        if (!el || el.dataset.counterAnimated === '1') return;
        el.dataset.counterAnimated = '1';

        const target = Number(el.dataset.citizenCounter || el.textContent || 0);
        if (!Number.isFinite(target)) return;

        if (prefersReducedMotion) {
            el.textContent = String(Math.round(target));
            return;
        }

        const duration = 1000;
        const startTime = performance.now();
        /* Elastic ease-out for a satisfying bounce */
        const elasticOut = (t) => {
            if (t === 0 || t === 1) return t;
            return Math.pow(2, -10 * t) * Math.sin((t - 0.075) * (2 * Math.PI) / 0.3) + 1;
        };

        const step = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = elasticOut(progress);
            el.textContent = String(Math.round(target * eased));
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = String(Math.round(target));
            }
        };

        requestAnimationFrame(step);
    };

    /* ── Intersection observer for reveals + counters ── */
    const revealObserver = !prefersReducedMotion && 'IntersectionObserver' in window
        ? new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                if (entry.target.matches('[data-citizen-counter]')) {
                    animateCounter(entry.target);
                }
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' })
        : null;

    /* ── Global UX initializer ── */
    window.initCitizenGlobalUX = function initCitizenGlobalUX(root = document) {
        const revealItems = root.querySelectorAll('.citizen-reveal, [data-citizen-reveal]');
        revealItems.forEach((el, index) => {
            if (el.dataset.citizenRevealBound === '1') return;
            el.dataset.citizenRevealBound = '1';
            /* Stagger: 60ms per item, max 360ms */
            el.style.transitionDelay = `${Math.min(index * 60, 360)}ms`;

            if (prefersReducedMotion || !revealObserver) {
                el.classList.add('is-visible');
            } else {
                revealObserver.observe(el);
            }
        });

        root.querySelectorAll('[data-citizen-counter]').forEach((counter) => {
            if (counter.dataset.counterBound === '1') return;
            counter.dataset.counterBound = '1';

            if (prefersReducedMotion || !revealObserver) {
                animateCounter(counter);
            } else {
                revealObserver.observe(counter);
            }
        });
    };

    window.initCitizenGlobalUX(document);

    /* ── Card tilt micro-interaction on hover ── */
    if (!prefersReducedMotion) {
        document.querySelectorAll('.citizen-kpi-card, .citizen-hero-panel').forEach((card) => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                card.style.transform = `perspective(600px) rotateY(${x * 4}deg) rotateX(${-y * 4}deg) translateY(-3px)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
            });
        });
    }
})();

(function () {
    const body = document.body;
    if (!body.classList.contains('es-role-office_user')) return;
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ── Topbar scroll shadow ── */
    const topbar = document.querySelector('.es-topbar');
    if (topbar) {
        let ticking = false;
        const syncTopbarState = () => {
            topbar.classList.toggle('is-scrolled', window.scrollY > 6);
            ticking = false;
        };
        syncTopbarState();
        window.addEventListener('scroll', () => {
            if (!ticking) { ticking = true; requestAnimationFrame(syncTopbarState); }
        }, { passive: true });
    }

    /* ── Elastic ease-out counter animation ── */
    const elasticEaseOut = (t) => {
        if (t === 0 || t === 1) return t;
        return Math.pow(2, -10 * t) * Math.sin((t - 0.075) * (2 * Math.PI) / 0.3) + 1;
    };

    const animateCounter = (el) => {
        if (!el || el.dataset.officeCounterAnimated === '1') return;
        el.dataset.officeCounterAnimated = '1';

        const target = Number(el.dataset.officeCounter || el.textContent || 0);
        if (!Number.isFinite(target)) return;

        if (prefersReducedMotion) {
            el.textContent = String(Math.round(target));
            return;
        }

        const duration = 900;
        const startTime = performance.now();
        const step = (now) => {
            const progress = Math.min((now - startTime) / duration, 1);
            const eased = elasticEaseOut(progress);
            el.textContent = String(Math.round(target * eased));
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = String(Math.round(target));
            }
        };

        requestAnimationFrame(step);
    };

    /* ── IntersectionObserver for reveals ── */
    const revealObserver = !prefersReducedMotion && 'IntersectionObserver' in window
        ? new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                if (entry.target.matches('[data-office-counter]')) {
                    animateCounter(entry.target);
                }
                observer.unobserve(entry.target);
            });
        }, { threshold: 0.12 })
        : null;

    window.initOfficeGlobalUX = function initOfficeGlobalUX(root = document) {
        /* Staggered reveals — wider delay */
        const revealItems = root.querySelectorAll('.office-reveal, [data-office-reveal]');
        revealItems.forEach((el, index) => {
            if (el.dataset.officeRevealBound === '1') return;
            el.dataset.officeRevealBound = '1';
            el.style.transitionDelay = `${Math.min(index * 60, 360)}ms`;

            if (prefersReducedMotion || !revealObserver) {
                el.classList.add('is-visible');
            } else {
                revealObserver.observe(el);
            }
        });

        /* Counters */
        root.querySelectorAll('[data-office-counter]').forEach((counter) => {
            if (counter.dataset.officeCounterBound === '1') return;
            counter.dataset.officeCounterBound = '1';

            if (prefersReducedMotion || !revealObserver) {
                animateCounter(counter);
            } else {
                revealObserver.observe(counter);
            }
        });

        /* ── Card tilt micro-interaction ── */
        if (!prefersReducedMotion) {
            root.querySelectorAll('.office-kpi-card, .office-hero-panel').forEach((card) => {
                if (card.dataset.officeTiltBound === '1') return;
                card.dataset.officeTiltBound = '1';
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = (e.clientX - rect.left) / rect.width - 0.5;
                    const y = (e.clientY - rect.top) / rect.height - 0.5;
                    card.style.transform = `perspective(600px) rotateY(${x * 4}deg) rotateX(${-y * 4}deg) translateY(-3px)`;
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                });
            });
        }
    };

    window.initOfficeGlobalUX(document);
})();
</script>

@stack('scripts')
</body>
</html>

