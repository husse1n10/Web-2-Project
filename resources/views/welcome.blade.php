<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CedarGov Platform for Lebanese municipalities.">
    <title>CedarGov — Lebanon</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital,wght@0,400;1,400&family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --cream: #F5F0E8;
            --cream-light: #FAF7F2;
            --ink: #1A1714;
            --teal: #0D9488;
            --teal-dark: #134E4A;
            --teal-light: #CCFBF1;
            --gold: #F6C453;
            --gold-light: #FEF3C7;
            --ease: cubic-bezier(0.22, 1, 0.36, 1);
            --border: rgba(0,0,0,0.07);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--cream);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            overflow-x: hidden;
        }

        /* ── Subtle dot-grid texture ────────── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            opacity: 0.35;
            background-image: radial-gradient(circle, rgba(26,23,20,0.06) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* ── NAV ──────────────────────────────── */
        .lp-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 200;
            background: rgba(245,240,232,0.65);
            backdrop-filter: blur(24px) saturate(1.4);
            -webkit-backdrop-filter: blur(24px) saturate(1.4);
            border-bottom: 1px solid transparent;
            padding: 0 2.5rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.35s var(--ease);
        }

        .lp-nav.scrolled {
            height: 56px;
            background: rgba(250,245,237,0.94);
            border-bottom-color: rgba(26,23,20,0.07);
            box-shadow: 0 4px 24px rgba(26,23,20,0.05);
        }

        .lp-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--ink);
        }

        .lp-brand-mark {
            width: 40px; height: 40px;
            border-radius: 11px;
            overflow: hidden;
            background: #1D1916;
            box-shadow: 0 4px 12px rgba(26,23,20,0.18);
            flex-shrink: 0;
        }

        .lp-brand-mark img { width: 100%; height: 100%; object-fit: cover; display: block; }

        .lp-brand-text { line-height: 1.16; }
        .lp-brand-text strong { display: block; font-size: 0.875rem; font-weight: 800; letter-spacing: -0.01em; }
        .lp-brand-text span { display: block; font-size: 0.625rem; color: #78716C; font-weight: 500; }

        .lp-nav-actions { display: flex; align-items: center; gap: 0.625rem; }

        .lp-btn-ghost {
            background: none;
            border: 1px solid transparent;
            font-size: 0.875rem; font-weight: 600;
            color: #57534E;
            padding: 0.45rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s var(--ease);
            font-family: 'Inter', sans-serif;
        }
        .lp-btn-ghost:hover { color: var(--ink); background: rgba(255,255,255,0.55); border-color: var(--border); }

        .lp-btn-black {
            background: var(--ink);
            color: #fff;
            border: none;
            font-size: 0.875rem; font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
            letter-spacing: -0.01em;
            box-shadow: 0 2px 8px rgba(26,23,20,0.16);
            transition: all 0.2s var(--ease);
        }
        .lp-btn-black:hover { background: #2D2926; color: #fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,23,20,0.2); }

        /* ── HERO ─────────────────────────────── */
        .lp-hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 7rem 0 5rem;
        }

        /* Warm ambient blobs */
        .lp-hero-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            pointer-events: none;
            z-index: 0;
            will-change: transform;
        }
        .lp-hero-blob-1 {
            width: 650px; height: 650px;
            top: -18%; right: -8%;
            background: radial-gradient(circle, rgba(13,148,136,0.14) 0%, transparent 70%);
            animation: bFloat1 18s ease-in-out infinite;
        }
        .lp-hero-blob-2 {
            width: 500px; height: 500px;
            top: 35%; right: 12%;
            background: radial-gradient(circle, rgba(246,196,83,0.16) 0%, transparent 70%);
            animation: bFloat2 22s ease-in-out infinite;
        }
        .lp-hero-blob-3 {
            width: 400px; height: 400px;
            bottom: -12%; left: -6%;
            background: radial-gradient(circle, rgba(246,196,83,0.1) 0%, transparent 70%);
            animation: bFloat3 20s ease-in-out infinite;
        }

        @keyframes bFloat1 {
            0%,100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(-25px,18px) scale(1.04); }
            66% { transform: translate(18px,-12px) scale(0.96); }
        }
        @keyframes bFloat2 {
            0%,100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(22px,-25px) scale(1.06); }
            66% { transform: translate(-28px,12px) scale(0.94); }
        }
        @keyframes bFloat3 {
            0%,100% { transform: translate(0,0); }
            50% { transform: translate(25px,-18px) scale(1.08); }
        }

        .lp-hero-inner {
            position: relative; z-index: 1;
            width: 100%; max-width: 1240px;
            margin: 0 auto; padding: 0 2.5rem;
            display: grid;
            grid-template-columns: 1fr 440px;
            align-items: center;
            gap: 4.5rem;
        }

        .lp-eyebrow {
            display: inline-flex; align-items: center; gap: 0.5rem;
            font-size: 0.68rem; font-weight: 700;
            letter-spacing: 0.12em; text-transform: uppercase;
            color: var(--teal-dark);
            background: rgba(13,148,136,0.07);
            border: 1px solid rgba(13,148,136,0.14);
            padding: 0.4rem 1rem;
            border-radius: 50px;
            margin-bottom: 1.75rem;
        }
        .lp-eyebrow-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--teal);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%,100% { opacity:1; transform:scale(1); }
            50% { opacity:.45; transform:scale(.8); }
        }

        .lp-headline {
            font-family: 'DM Serif Display', Georgia, serif;
            font-style: italic;
            font-weight: 400;
            font-size: clamp(3.25rem, 5.8vw, 5.25rem);
            line-height: 1.02;
            letter-spacing: -0.025em;
            color: var(--ink);
            margin-bottom: 1.75rem;
        }

        .lp-headline em {
            font-style: italic;
        }
        .lp-headline em .word {
            background: linear-gradient(135deg, var(--teal-dark) 0%, var(--teal) 45%, #2DD4BF 65%, var(--gold) 100%);
            background-size: 250% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradShift 6s ease-in-out infinite;
        }
        @keyframes gradShift {
            0%,100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .lp-sub {
            font-size: 1.0625rem; line-height: 1.75;
            color: #78716C;
            max-width: 480px;
            margin-bottom: 2.5rem;
        }

        .lp-cta { display: flex; align-items: center; gap: 0.875rem; flex-wrap: wrap; }

        .lp-cta-primary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: var(--ink); color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.9375rem; font-weight: 600;
            padding: 0.85rem 1.75rem;
            border-radius: 10px;
            text-decoration: none;
            letter-spacing: -0.01em;
            box-shadow: 0 6px 20px rgba(26,23,20,0.18);
            transition: all 0.25s var(--ease);
        }
        .lp-cta-primary:hover { background: #2D2926; color: #fff; transform: translateY(-2px); box-shadow: 0 12px 32px rgba(26,23,20,0.22); }
        .lp-cta-primary .arrow { font-size: 1.1rem; transition: transform 0.25s var(--ease); }
        .lp-cta-primary:hover .arrow { transform: translateX(4px); }

        .lp-cta-secondary {
            font-size: 0.9375rem; font-weight: 600;
            color: #57534E;
            text-decoration: none;
            padding: 0.85rem 1.125rem;
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.2s var(--ease);
            font-family: 'Inter', sans-serif;
        }
        .lp-cta-secondary:hover { color: var(--ink); background: rgba(255,255,255,0.55); border-color: var(--border); }

        /* ── PLATFORM CARD ────────────────────── */
        .lp-card-wrap {
            position: relative;
            perspective: 1200px;
        }
        .lp-card-wrap::before {
            content: '';
            position: absolute; inset: -18px;
            background: radial-gradient(circle at 50% 0%, rgba(13,148,136,0.16), transparent 55%);
            filter: blur(18px);
            z-index: -1;
            pointer-events: none;
        }

        .lp-card {
            background: #fff;
            border: 1px solid rgba(0,0,0,0.07);
            border-radius: 18px;
            overflow: hidden;
            box-shadow:
                0 2px 4px rgba(0,0,0,0.03),
                0 10px 28px rgba(0,0,0,0.07),
                0 28px 64px rgba(0,0,0,0.06);
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.1s ease-out, box-shadow 0.3s var(--ease);
            will-change: transform;
        }
        .lp-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--teal) 0%, var(--gold) 70%, rgba(246,196,83,0.3) 100%);
            z-index: 2;
        }

        .lp-card-shine {
            position: absolute; inset: 0;
            background: radial-gradient(600px circle at var(--mx,50%) var(--my,50%), rgba(255,255,255,0.12), transparent 40%);
            pointer-events: none; z-index: 3;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .lp-card:hover .lp-card-shine { opacity: 1; }

        .lp-card-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.125rem 1.5rem;
            border-bottom: 1px solid #F0EDE8;
        }
        .lp-card-title { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #A8A29E; }

        .lp-live { display: inline-flex; align-items: center; gap: 0.375rem; font-size: 0.72rem; font-weight: 600; color: var(--teal); }
        .lp-live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--teal); animation: pulse 2s ease-in-out infinite; box-shadow: 0 0 6px rgba(13,148,136,0.35); }

        .lp-stats { display: grid; grid-template-columns: 1fr 1fr; }
        .lp-stat {
            padding: 1.375rem 1.5rem;
            border-right: 1px solid #F0EDE8;
            border-bottom: 1px solid #F0EDE8;
            transition: background 0.2s ease;
        }
        .lp-stat:nth-child(even) { border-right: none; }
        .lp-stat:nth-last-child(-n+2) { border-bottom: none; }
        .lp-stat:hover { background: rgba(252,248,242,0.85); }
        .lp-stat-label { font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #A8A29E; margin-bottom: 0.375rem; }
        .lp-stat-value { font-size: 2.125rem; font-weight: 900; line-height: 1; color: var(--ink); letter-spacing: -0.03em; }
        .lp-stat-value.accent { color: var(--teal); }

        .lp-activity-hd { padding: 0.875rem 1.5rem 0.5rem; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #A8A29E; border-top: 1px solid #F0EDE8; }

        .lp-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.625rem 1.5rem;
            border-top: 1px solid #F5F2EF;
            transition: background 0.2s ease;
        }
        .lp-row:hover { background: rgba(249,246,241,0.8); }
        .lp-row-ref { font-size: 0.8125rem; font-weight: 600; color: var(--ink); letter-spacing: -0.01em; }
        .lp-row-type { font-size: 0.72rem; color: #A8A29E; margin-top: 0.1rem; }

        .chip {
            font-size: 0.6rem; font-weight: 700; letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.3em 0.75em;
            border-radius: 50px;
            border: 1px solid transparent;
            white-space: nowrap;
        }
        .chip-approved { background:#CCFBF1; color:#0F766E; border-color:#99F6E4; }
        .chip-inreview { background:#E0F2FE; color:#0369A1; border-color:#BAE6FD; }
        .chip-pending  { background:#FEF3C7; color:#92400E; border-color:#FDE68A; }

        /* ── TRUST BAR ────────────────────────── */
        .lp-trust {
            background: var(--cream-light);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 2.75rem 2.5rem;
            position: relative;
            z-index: 1;
        }
        .lp-trust-inner {
            max-width: 1240px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            text-align: center;
        }
        .lp-trust-item {
            padding: 0.5rem;
        }
        .lp-trust-num {
            font-family: 'DM Serif Display', Georgia, serif;
            font-size: 2.75rem;
            font-weight: 400;
            color: var(--teal-dark);
            line-height: 1;
            margin-bottom: 0.35rem;
            letter-spacing: -0.02em;
        }
        .lp-trust-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #78716C;
            letter-spacing: 0.02em;
        }

        /* ── SECTIONS ─────────────────────────── */
        .lp-section {
            padding: clamp(4.5rem, 7vw, 6.5rem) 0;
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        .lp-section-inner {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 2.5rem;
        }

        .lp-tag {
            display: inline-flex; align-items: center; gap: 0.4rem;
            font-size: 0.65rem; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase;
            background: var(--teal-light);
            color: #0F766E;
            padding: 0.35rem 0.8rem;
            border-radius: 50px;
            margin-bottom: 1rem;
            border: 1px solid #9DE7DC;
        }

        .lp-section-title {
            font-family: 'DM Serif Display', Georgia, serif;
            font-style: italic; font-weight: 400;
            font-size: clamp(1.85rem, 3.2vw, 2.625rem);
            color: var(--ink);
            margin-bottom: 0.625rem;
            letter-spacing: -0.015em;
            line-height: 1.12;
        }

        .lp-section-desc {
            font-size: 0.9375rem;
            color: #78716C;
            line-height: 1.72;
            max-width: 520px;
            margin-bottom: 2.75rem;
        }

        /* ── FEATURE CARDS ────────────────────── */
        .lp-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.125rem;
        }

        .lp-feature {
            background: #fff;
            border: 1px solid #EAE6DF;
            border-radius: 18px;
            padding: 0;
            position: relative;
            overflow: hidden;
            transition: all 0.35s var(--ease);
        }

        .lp-feature-accent {
            height: 4px;
            width: 100%;
            border-radius: 18px 18px 0 0;
        }
        .lp-feature-accent.ac-t { background: linear-gradient(90deg, #0D9488, #5EEAD4); }
        .lp-feature-accent.ac-s { background: linear-gradient(90deg, #0284C7, #7DD3FC); }
        .lp-feature-accent.ac-a { background: linear-gradient(90deg, #D97706, #FCD34D); }
        .lp-feature-accent.ac-v { background: linear-gradient(90deg, #7C3AED, #C4B5FD); }
        .lp-feature-accent.ac-e { background: linear-gradient(90deg, #059669, #6EE7B7); }
        .lp-feature-accent.ac-r { background: linear-gradient(90deg, #E11D48, #FDA4AF); }

        .lp-feature-body {
            padding: 1.75rem;
        }

        .lp-feature::after {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: 19px;
            padding: 1px;
            background: linear-gradient(135deg, var(--teal), var(--gold));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.35s ease;
            pointer-events: none;
        }

        .lp-feature:hover {
            transform: translateY(-7px);
            box-shadow: 0 24px 56px rgba(26,23,20,0.10);
        }
        .lp-feature:hover::after { opacity: 1; }

        .lp-fi {
            width: 54px; height: 54px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.35rem;
            margin-bottom: 1.25rem;
            position: relative;
        }
        .lp-fi::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 18px;
            opacity: 0.15;
            z-index: -1;
            transition: opacity 0.3s ease;
        }
        .fi-t { background:#CCFBF1; color:#0F766E; }
        .fi-t::after { background: #0D9488; }
        .fi-s { background:#E0F2FE; color:#0369A1; }
        .fi-s::after { background: #0284C7; }
        .fi-a { background:#FEF3C7; color:#B45309; }
        .fi-a::after { background: #D97706; }
        .fi-v { background:#EDE9FE; color:#6D28D9; }
        .fi-v::after { background: #7C3AED; }
        .fi-e { background:#D1FAE5; color:#047857; }
        .fi-e::after { background: #059669; }
        .fi-r { background:#FFE4E6; color:#BE123C; }
        .fi-r::after { background: #E11D48; }

        .lp-feature:hover .lp-fi::after { opacity: 0.25; }

        .lp-feature h3 { font-size: 1rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem; letter-spacing: -0.01em; }
        .lp-feature p { font-size: 0.85rem; color: #78716C; line-height: 1.7; margin: 0; }

        /* ── STEPS ─────────────────────────────── */
        .lp-steps-section {
            background: #fff;
            border-top: 1px solid #EAE6DF;
            border-bottom: 1px solid #EAE6DF;
        }

        .lp-steps-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 4rem;
            align-items: start;
        }

        .lp-steps-list { position: relative; }
        .lp-steps-list::before {
            content: '';
            position: absolute;
            left: 19px; top: 38px; bottom: 38px;
            width: 2px;
            background: #E2E8F0;
            border-radius: 2px;
        }

        .lp-step {
            display: flex; gap: 1.375rem;
            padding: 1.5rem 0;
            align-items: flex-start;
            transition: transform 0.22s var(--ease);
            position: relative;
            z-index: 2;
        }
        .lp-step + .lp-step { border-top: 1px solid #F5F2EF; }
        .lp-step:hover { transform: translateX(6px); }

        .lp-step-n {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: #CBD5E1;
            color: #fff;
            font-size: 0.8125rem; font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(15,23,42,0.08);
            transition: all 0.3s var(--ease);
            position: relative; z-index: 2;
        }
        .lp-step:hover .lp-step-n { background: var(--teal-dark); box-shadow: 0 8px 20px rgba(13,148,136,0.2); transform: scale(1.06); }

        .lp-step h4 { font-size: 0.9375rem; font-weight: 700; color: var(--ink); margin-bottom: 0.35rem; letter-spacing: -0.01em; }
        .lp-step p { font-size: 0.84rem; color: #78716C; line-height: 1.65; margin: 0; }

        /* ── ROLES ─────────────────────────────── */
        .lp-grid-roles {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.125rem;
        }

        .lp-role {
            background: #fff;
            border: 1px solid #EAE6DF;
            border-radius: 18px;
            padding: 0;
            position: relative;
            overflow: hidden;
            transition: all 0.35s var(--ease);
            display: flex;
            flex-direction: column;
        }
        .lp-role::after {
            content: '';
            position: absolute; inset: -1px;
            border-radius: 19px; padding: 1px;
            background: linear-gradient(135deg, var(--teal), var(--gold));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.35s ease;
            pointer-events: none;
        }
        .lp-role:hover { transform: translateY(-7px); box-shadow: 0 24px 56px rgba(26,23,20,0.10); }
        .lp-role:hover::after { opacity: 1; }

        .lp-role-header {
            padding: 1.75rem 1.75rem 1.25rem;
            position: relative;
        }
        .lp-role-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 1.75rem; right: 1.75rem;
            height: 1px;
            background: linear-gradient(90deg, transparent, #EAE6DF, transparent);
        }

        .lp-role-header.rh-citizen { background: linear-gradient(135deg, #F0FDFA 0%, #fff 100%); }
        .lp-role-header.rh-office { background: linear-gradient(135deg, #EFF6FF 0%, #fff 100%); }
        .lp-role-header.rh-admin { background: linear-gradient(135deg, #FFFBEB 0%, #fff 100%); }

        .lp-role-icon {
            width: 50px; height: 50px;
            border-radius: 13px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.875rem;
        }

        .lp-role h3 { font-size: 1.0625rem; font-weight: 700; color: var(--ink); margin-bottom: 0.3rem; letter-spacing: -0.01em; }
        .lp-role-subtitle { font-size: 0.8rem; color: #A8A29E; font-weight: 500; margin: 0; }

        .lp-role-list {
            padding: 1.25rem 1.75rem 1.75rem;
            flex: 1;
        }
        .lp-role ul { list-style: none; padding: 0; margin: 0; }
        .lp-role li {
            font-size: 0.85rem; color: #57534E;
            padding: 0.425rem 0;
            display: flex; align-items: center; gap: 0.6rem;
            line-height: 1.5;
        }
        .lp-role li i { color: var(--teal); font-size: 0.75rem; flex-shrink: 0; }

        /* ── MARQUEE ──────────────────────────── */
        .lp-marquee {
            padding: 1rem 0;
            background: var(--ink);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }
        .lp-marquee-track {
            display: flex;
            animation: mScroll 35s linear infinite;
            width: max-content;
        }
        .lp-marquee-track:hover { animation-play-state: paused; }
        .lp-marquee-item {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0 2.25rem;
            font-size: 0.8rem; font-weight: 600;
            color: rgba(255,255,255,0.6);
            white-space: nowrap;
        }
        .lp-marquee-item i { color: var(--teal); font-size: 0.7rem; }
        .lp-marquee-sep { color: rgba(255,255,255,0.12); padding: 0 0.5rem; font-size: 0.45rem; }

        @keyframes mScroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* ── CTA BANNER ───────────────────────── */
        .lp-banner {
            border-radius: 20px;
            padding: 4.5rem 3.5rem;
            color: #fff;
            position: relative;
            overflow: visible;
            background: linear-gradient(135deg, #1A1714 0%, #1F1D1A 40%, #252320 100%);
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 24px 60px rgba(26,23,20,0.18);
        }

        .lp-banner-glow {
            position: absolute; inset: 0;
            overflow: hidden; pointer-events: none;
        }
        .lp-banner-glow span {
            position: absolute; border-radius: 50%;
            filter: blur(70px);
            animation: glow 12s ease-in-out infinite;
        }
        .lp-banner-glow span:nth-child(1) {
            width: 380px; height: 380px;
            top: -120px; right: -60px;
            background: rgba(13,148,136,0.22);
            animation-duration: 14s;
        }
        .lp-banner-glow span:nth-child(2) {
            width: 320px; height: 320px;
            bottom: -130px; left: -50px;
            background: rgba(246,196,83,0.18);
            animation-duration: 17s;
            animation-direction: reverse;
        }
        .lp-banner-glow span:nth-child(3) {
            width: 220px; height: 220px;
            top: 40%; right: 25%;
            background: rgba(45,212,191,0.1);
            animation-duration: 20s;
        }

        @keyframes glow {
            0%,100% { transform: translate(0,0) scale(1); }
            33% { transform: translate(18px,-12px) scale(1.08); }
            66% { transform: translate(-12px,8px) scale(0.92); }
        }

        .lp-banner h2 {
            font-family: 'DM Serif Display', Georgia, serif;
            font-style: italic; font-weight: 400;
            font-size: clamp(1.85rem, 3vw, 2.5rem);
            margin-bottom: 0.875rem;
            position: relative; z-index: 1;
            letter-spacing: -0.015em;
        }
        .lp-banner p {
            color: rgba(255,255,255,0.6);
            font-size: 0.9375rem; line-height: 1.72;
            max-width: 440px;
            margin-bottom: 2.25rem;
            position: relative; z-index: 1;
        }
        .lp-banner-btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: #fff; color: var(--ink);
            font-size: 0.9375rem; font-weight: 700;
            padding: 0.85rem 1.875rem;
            border-radius: 10px;
            text-decoration: none;
            position: relative; z-index: 1;
            box-shadow: 0 6px 20px rgba(0,0,0,0.18);
            transition: all 0.25s var(--ease);
            letter-spacing: -0.01em;
        }
        .lp-banner-btn:hover { background: var(--cream); color: var(--ink); transform: translateY(-2px); box-shadow: 0 10px 28px rgba(0,0,0,0.22); }
        .lp-banner-btn .arrow { transition: transform 0.25s var(--ease); }
        .lp-banner-btn:hover .arrow { transform: translateX(4px); }

        .lp-banner-cedar {
            position: absolute;
            right: -8%;
            top: 50%;
            transform: translateY(-50%) rotate(6deg);
            height: 140%;
            width: auto;
            opacity: 0.09;
            pointer-events: none;
            z-index: 0;
            filter: brightness(10);
        }

        /* ── FOOTER ────────────────────────────── */
        .lp-footer {
            background: var(--ink);
            color: rgba(255,255,255,0.6);
            padding: 0;
            position: relative; z-index: 1;
            margin-bottom: 0;
        }
        .lp-footer-inner { max-width: 1240px; margin: 0 auto; }

        /* Accent line at top */
        .lp-footer-accent {
            height: 1px;
            background: rgba(255,255,255,0.08);
        }

        /* Back to top bar */
        .lp-footer-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 2.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .lp-footer-top-bar-label {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.25);
        }
        .lp-back-top {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(255,255,255,0.4);
            text-decoration: none;
            padding: 0.4rem 0.85rem;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.25s var(--ease);
        }
        .lp-back-top:hover {
            color: rgba(255,255,255,0.85);
            border-color: rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.05);
            transform: translateY(-1px);
        }
        .lp-back-top i { font-size: 0.7rem; transition: transform 0.25s var(--ease); }
        .lp-back-top:hover i { transform: translateY(-2px); }

        /* Main footer content */
        .lp-footer-main {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 3.5rem;
            padding: 3.5rem 2.5rem 3rem;
        }

        .lp-footer-brand-text {
            font-size: 0.84rem;
            line-height: 1.7;
            margin-top: 1.125rem;
            color: rgba(255,255,255,0.35);
        }

        /* Contact info */
        .lp-footer-contact {
            margin-top: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }
        .lp-footer-contact-item {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.45);
        }
        .lp-footer-contact-item i {
            font-size: 0.75rem;
            color: var(--teal);
            width: 16px;
            text-align: center;
            flex-shrink: 0;
        }

        .lp-footer-links {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }
        .lp-footer-col h4 {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.3);
            margin-bottom: 1.125rem;
            position: relative;
            padding-bottom: 0.75rem;
        }
        .lp-footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 24px; height: 2px;
            background: var(--teal);
            border-radius: 2px;
            opacity: 0.5;
        }
        .lp-footer-col a {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.84rem;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            padding: 0.35rem 0;
            transition: all 0.2s var(--ease);
        }
        .lp-footer-col a::before {
            content: '';
            width: 0;
            height: 1px;
            background: var(--teal);
            transition: width 0.25s var(--ease);
            flex-shrink: 0;
        }
        .lp-footer-col a:hover { color: rgba(255,255,255,0.95); padding-left: 4px; }
        .lp-footer-col a:hover::before { width: 12px; }

        /* Divider */
        .lp-footer-divider {
            height: 1px;
            margin: 0 2.5rem;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
        }

        /* Bottom bar */
        .lp-footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2.5rem;
            font-size: 0.78rem;
        }
        .lp-footer-bottom-left {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }
        .lp-footer-bottom-left img { width: 20px; height: 20px; border-radius: 5px; }

        .lp-footer-socials { display: flex; gap: 0.5rem; }
        .lp-footer-socials a {
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
            color: rgba(255,255,255,0.4);
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.25s var(--ease);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .lp-footer-socials a:hover {
            background: var(--teal);
            color: #fff;
            border-color: var(--teal);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(13,148,136,0.3);
        }

        /* ── REVEAL ────────────────────────────── */
        [data-reveal] { opacity: 1; filter: none; transform: none; }

        .js-motion [data-reveal] {
            opacity: 0;
            filter: blur(2px);
            transform: translate3d(0, 26px, 0);
            transition:
                opacity 0.65s var(--ease),
                transform 0.65s var(--ease),
                filter 0.65s var(--ease);
            transition-delay: var(--rv, 0ms);
            will-change: transform, opacity;
        }
        .js-motion [data-reveal="left"]  { transform: translate3d(-26px,0,0); }
        .js-motion [data-reveal="right"] { transform: translate3d(26px,0,0); }
        .js-motion [data-reveal="scale"] { transform: translate3d(0,22px,0) scale(0.97); }
        .js-motion [data-reveal].is-visible { opacity: 1; filter: blur(0); transform: translate3d(0,0,0) scale(1); }

        /* ── FOCUS ─────────────────────────────── */
        a:focus-visible, button:focus-visible {
            outline: 2px solid rgba(13,148,136,0.5);
            outline-offset: 2px;
        }

        /* ── SECTION PARALLAX DEPTH ──────────── */
        .lp-section-depth {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .lp-depth-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0;
            transition: opacity 0.8s ease;
            will-change: transform;
        }
        .lp-section.depth-visible .lp-depth-orb { opacity: 1; }
        .lp-depth-orb--teal {
            width: 320px; height: 320px;
            background: radial-gradient(circle, rgba(13,148,136,0.10) 0%, transparent 70%);
            top: -60px; left: -80px;
        }
        .lp-depth-orb--gold {
            width: 260px; height: 260px;
            background: radial-gradient(circle, rgba(246,196,83,0.10) 0%, transparent 70%);
            bottom: -40px; right: -60px;
        }
        .lp-depth-orb--purple {
            width: 280px; height: 280px;
            background: radial-gradient(circle, rgba(109,40,217,0.07) 0%, transparent 70%);
            top: 30%; right: 10%;
        }

        /* ── SCROLL PROGRESS BAR ──────────────── */
        .scroll-progress {
            position: fixed;
            top: 0; left: 0;
            width: 0%;
            height: 2px;
            background: var(--teal);
            z-index: 9999;
            transition: width 0.08s linear;
            opacity: 0.7;
        }

        /* ── FLOATING GEOMETRIC ACCENTS ───────── */
        .lp-hero-shapes {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .lp-shape {
            position: absolute;
            will-change: transform;
        }
        .lp-shape-circle {
            border: 2px solid var(--teal);
            border-radius: 50%;
            opacity: 0.07;
        }
        .lp-shape-triangle {
            width: 0; height: 0;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 14px solid var(--teal-dark);
            opacity: 0.06;
        }
        .lp-shape-diamond {
            background: var(--teal-dark);
            transform: rotate(45deg);
            opacity: 0.05;
        }
        .lp-shape-ring {
            border: 1.5px solid var(--gold);
            border-radius: 50%;
            opacity: 0.06;
        }
        .lp-shape-dot {
            background: var(--teal-dark);
            border-radius: 50%;
            opacity: 0.08;
        }
        .lp-shape-square {
            border: 1.5px solid var(--teal-dark);
            border-radius: 3px;
            opacity: 0.05;
        }

        .lp-shape-1 { width: 18px; height: 18px; top: 14%; left: 7%; animation: shapeOrbit1 18s ease-in-out infinite; }
        .lp-shape-2 { top: 24%; right: 18%; animation: shapeOrbit2 22s ease-in-out infinite; }
        .lp-shape-3 { width: 12px; height: 12px; top: 62%; left: 10%; animation: shapeOrbit3 24s ease-in-out infinite; }
        .lp-shape-4 { width: 20px; height: 20px; top: 74%; right: 7%; animation: shapeOrbit1 20s ease-in-out infinite reverse; }
        .lp-shape-5 { width: 6px; height: 6px; top: 40%; left: 4%; animation: shapeOrbit2 16s ease-in-out infinite; }
        .lp-shape-6 { width: 14px; height: 14px; top: 52%; right: 4%; animation: shapeOrbit3 19s ease-in-out infinite; }

        @keyframes shapeOrbit1 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(20px, -15px) rotate(90deg); }
            50% { transform: translate(-10px, 18px) rotate(180deg); }
            75% { transform: translate(15px, 8px) rotate(270deg); }
        }
        @keyframes shapeOrbit2 {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-18px, 12px) rotate(120deg); }
            66% { transform: translate(12px, -18px) rotate(240deg); }
        }
        @keyframes shapeOrbit3 {
            0%, 100% { transform: translate(0, 0) rotate(45deg); }
            50% { transform: translate(16px, -22px) rotate(225deg); }
        }

        /* ── HERO TEXT STAGGER ────────────────── */
        .lp-headline .word {
            display: inline-block;
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.65s var(--ease), transform 0.65s var(--ease);
        }
        .lp-headline.text-revealed .word {
            opacity: 1;
            transform: translateY(0);
        }

        /* ── FEATURE ICON HOVER ───────────────── */
        .lp-feature:hover .lp-fi i {
            animation: iconPop 0.45s var(--ease);
        }
        @keyframes iconPop {
            0% { transform: scale(1); }
            40% { transform: scale(1.2) rotate(-4deg); }
            70% { transform: scale(0.95); }
            100% { transform: scale(1) rotate(0); }
        }

        /* ── ROLE CARD SHINE SWEEP ────────────── */
        .lp-role::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(
                60deg,
                transparent 42%,
                rgba(255,255,255,0.04) 46%,
                rgba(255,255,255,0.07) 50%,
                rgba(255,255,255,0.04) 54%,
                transparent 58%
            );
            transform: translateX(-100%);
            z-index: 2;
            pointer-events: none;
        }
        .lp-role:hover::before {
            transform: translateX(100%);
            transition: transform 0.7s var(--ease);
        }

        /* ── STEPS SCROLL-LINKED LINE ────────── */
        .lp-steps-list::after {
            content: '';
            position: absolute;
            left: 19px; top: 38px; bottom: 38px;
            width: 2px;
            background: linear-gradient(180deg, var(--teal) 0%, var(--teal-dark) 100%);
            border-radius: 2px;
            transform-origin: top;
            transform: scaleY(var(--line-progress, 0));
            z-index: 1;
        }
        .lp-step.step-reached .lp-step-n {
            background: var(--teal);
            box-shadow: 0 5px 18px rgba(13,148,136,0.28);
            transform: scale(1.08);
        }

        /* ── STAT VALUE GLOW ON COUNT ─────────── */
        .lp-trust-num.is-counting,
        .lp-stat-value.is-counting {
            text-shadow: 0 0 20px rgba(13,148,136,0.25);
            transition: text-shadow 0.3s ease;
        }

        /* ── CTA SHIMMER ──────────────────────── */
        .lp-cta-primary {
            position: relative;
            overflow: hidden;
        }
        .lp-cta-primary::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            transform: translateX(-100%);
            animation: ctaShimmer 5s ease-in-out infinite;
        }
        @keyframes ctaShimmer {
            0%, 75%, 100% { transform: translateX(-100%); }
            50% { transform: translateX(100%); }
        }

        /* ── BANNER SUBTLE FLOAT ──────────────── */
        .lp-banner {
            animation: bannerFloat 8s ease-in-out infinite;
        }
        @keyframes bannerFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        /* ── RESPONSIVE ───────────────────────── */
        @media (max-width: 1023px) {
            .lp-hero-inner { grid-template-columns: 1fr; gap: 3rem; }
            .lp-card { max-width: 480px; }
            .lp-grid-3 { grid-template-columns: repeat(2, 1fr); }
            .lp-steps-layout { grid-template-columns: 1fr; gap: 2.5rem; }
            .lp-grid-roles { grid-template-columns: repeat(2, 1fr); }
            .lp-trust-inner { grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
            .lp-footer-main { grid-template-columns: 1fr; gap: 2.5rem; }
        }

        @media (max-width: 639px) {
            .lp-nav { padding: 0 1.25rem; }
            .lp-brand-mark { width: 36px; height: 36px; border-radius: 10px; }
            .lp-brand-text strong { font-size: .81rem; }
            .lp-brand-text span { font-size: .58rem; }
            .lp-hero-inner { padding: 0 1.25rem; }
            .lp-section-inner { padding: 0 1.25rem; }
            .lp-headline { font-size: 2.75rem; }
            .lp-grid-3 { grid-template-columns: 1fr; }
            .lp-grid-roles { grid-template-columns: 1fr; }
            .lp-banner { padding: 2.5rem 1.75rem; border-radius: 16px; }
            .lp-banner-cedar { height: 100%; right: -10%; opacity: 0.05; }
            .lp-footer-main { padding: 2.5rem 1.25rem; gap: 2rem; }
            .lp-footer-top-bar { padding: 1rem 1.25rem; }
            .lp-footer-links { grid-template-columns: repeat(2, 1fr); }
            .lp-footer-bottom { flex-direction: column; gap: 1rem; text-align: center; padding: 1.25rem; }
            .lp-footer-divider { margin: 0 1.25rem; }
            .lp-hero { padding: 5.5rem 0 3rem; min-height: auto; }
            .lp-trust-inner { grid-template-columns: repeat(2, 1fr); }
            .lp-trust { padding: 2rem 1.25rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
            }
            [data-reveal] { opacity: 1; filter: none; transform: none; }
        }
    </style>
</head>
<body>

{{-- ── SCROLL PROGRESS ─────────────────────────── --}}
<div class="scroll-progress" id="scrollProgress"></div>

{{-- ── NAV ──────────────────────────────────────── --}}
<nav class="lp-nav" id="lpNav" aria-label="Primary navigation">
    <a href="{{ route('home') }}" class="lp-brand" data-reveal="left">
        <span class="lp-brand-mark">
            <img src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}" alt="CedarGov icon">
        </span>
        <div class="lp-brand-text">
            <strong>CedarGov</strong>
            <span>Lebanon Gov Portal</span>
        </div>
    </a>
    <div class="lp-nav-actions" data-reveal="right">
        <a href="{{ route('login') }}" class="lp-btn-ghost">Sign in</a>
        <a href="{{ route('register') }}" class="lp-btn-black">Get started</a>
    </div>
</nav>

{{-- ── HERO ──────────────────────────────────────── --}}
<section class="lp-hero">
    <div class="lp-hero-blob lp-hero-blob-1"></div>
    <div class="lp-hero-blob lp-hero-blob-2"></div>
    <div class="lp-hero-blob lp-hero-blob-3"></div>

    {{-- Floating geometric accents --}}
    <div class="lp-hero-shapes">
        <div class="lp-shape lp-shape-circle lp-shape-1"></div>
        <div class="lp-shape lp-shape-triangle lp-shape-2"></div>
        <div class="lp-shape lp-shape-diamond lp-shape-3"></div>
        <div class="lp-shape lp-shape-ring lp-shape-4"></div>
        <div class="lp-shape lp-shape-dot lp-shape-5"></div>
        <div class="lp-shape lp-shape-square lp-shape-6"></div>
    </div>

    <div class="lp-hero-inner">
        <div data-reveal="left">
            <div class="lp-eyebrow">
                <span class="lp-eyebrow-dot"></span>
                CedarGov &mdash; Lebanon
            </div>

            <h1 class="lp-headline" id="heroHeadline">
                <span class="word" style="--wi:0">Government</span><br>
                <span class="word" style="--wi:1">services,</span> <span class="word" style="--wi:2">made</span><br>
                <em><span class="word" style="--wi:3">simple.</span></em>
            </h1>

            <p class="lp-sub">
                Submit requests, upload documents, pay fees, and track progress
                through a single platform built for Lebanese municipalities.
                No queues, no paperwork.
            </p>

            <div class="lp-cta">
                <a href="{{ route('register') }}" class="lp-cta-primary">
                    Create free account <span class="arrow">&rarr;</span>
                </a>
                <a href="{{ route('login') }}" class="lp-cta-secondary">Sign in</a>
            </div>
        </div>

        <div class="lp-card-wrap" data-reveal="right" data-delay="120" id="cardWrap">
            <div class="lp-card" id="tiltCard">
                <div class="lp-card-shine"></div>
                <div class="lp-card-header">
                    <span class="lp-card-title">Platform Overview</span>
                    <span class="lp-live"><span class="lp-live-dot"></span> Live</span>
                </div>
                <div class="lp-stats">
                    <div class="lp-stat">
                        <div class="lp-stat-label">Municipalities</div>
                        <div class="lp-stat-value accent" data-counter="3">3</div>
                    </div>
                    <div class="lp-stat">
                        <div class="lp-stat-label">Active Offices</div>
                        <div class="lp-stat-value" data-counter="3">3</div>
                    </div>
                    <div class="lp-stat">
                        <div class="lp-stat-label">Services</div>
                        <div class="lp-stat-value" data-counter="21">21</div>
                    </div>
                    <div class="lp-stat">
                        <div class="lp-stat-label">Requests</div>
                        <div class="lp-stat-value" data-counter="0">0</div>
                    </div>
                </div>
                <div class="lp-activity-hd">Recent Activity</div>
                <div class="lp-row">
                    <div><div class="lp-row-ref">SRQ-2024-00041</div><div class="lp-row-type">Birth Certificate</div></div>
                    <span class="chip chip-approved">Approved</span>
                </div>
                <div class="lp-row">
                    <div><div class="lp-row-ref">SRQ-2024-00038</div><div class="lp-row-type">Building Permit</div></div>
                    <span class="chip chip-inreview">In Review</span>
                </div>
                <div class="lp-row">
                    <div><div class="lp-row-ref">SRQ-2024-00031</div><div class="lp-row-type">Land Registration</div></div>
                    <span class="chip chip-pending">Pending</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── TRUST / STATS BAR ─────────────────────────── --}}
<section class="lp-trust" data-reveal="scale">
    <div class="lp-trust-inner">
        <div class="lp-trust-item">
            <div class="lp-trust-num" data-counter="3">3</div>
            <div class="lp-trust-label">Municipalities Connected</div>
        </div>
        <div class="lp-trust-item">
            <div class="lp-trust-num" data-counter="21">21</div>
            <div class="lp-trust-label">Services Available</div>
        </div>
        <div class="lp-trust-item">
            <div class="lp-trust-num" data-counter="3">3</div>
            <div class="lp-trust-label">Active Offices</div>
        </div>
        <div class="lp-trust-item">
            <div class="lp-trust-num">24/7</div>
            <div class="lp-trust-label">Platform Availability</div>
        </div>
    </div>
</section>

{{-- ── MARQUEE ───────────────────────────────────── --}}
<div class="lp-marquee">
    <div class="lp-marquee-track">
        <span class="lp-marquee-item"><i class="bi bi-shield-check"></i> Secure &amp; Encrypted</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-lightning-charge"></i> Real-time Updates</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-globe2"></i> Multi-language</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-credit-card"></i> Online Payments</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-qr-code"></i> QR Tracking</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-file-earmark-check"></i> Digital Documents</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-calendar-check"></i> Appointments</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-bell"></i> Push Notifications</span>
        <span class="lp-marquee-sep">&bull;</span>
        {{-- duplicate for seamless loop --}}
        <span class="lp-marquee-item"><i class="bi bi-shield-check"></i> Secure &amp; Encrypted</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-lightning-charge"></i> Real-time Updates</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-globe2"></i> Multi-language</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-credit-card"></i> Online Payments</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-qr-code"></i> QR Tracking</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-file-earmark-check"></i> Digital Documents</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-calendar-check"></i> Appointments</span>
        <span class="lp-marquee-sep">&bull;</span>
        <span class="lp-marquee-item"><i class="bi bi-bell"></i> Push Notifications</span>
        <span class="lp-marquee-sep">&bull;</span>
    </div>
</div>

{{-- ── FEATURES ──────────────────────────────────── --}}
<section class="lp-section lp-parallax-section" style="background: var(--cream-light); border-bottom:1px solid #EAE6DF;">
    <div class="lp-section-depth">
        <div class="lp-depth-orb lp-depth-orb--teal" data-parallax-speed="0.035"></div>
        <div class="lp-depth-orb lp-depth-orb--gold" data-parallax-speed="0.025"></div>
    </div>
    <div class="lp-section-inner">
        <span class="lp-tag" data-reveal="left"><i class="bi bi-grid-3x3-gap-fill"></i> Platform capabilities</span>
        <h2 class="lp-section-title" data-reveal="left" data-delay="40">Everything you need in one place</h2>
        <p class="lp-section-desc" data-reveal="left" data-delay="80">A complete digital workflow replacing in-person visits with secure, trackable online processes.</p>

        <div class="lp-grid-3">
            <div class="lp-feature" data-reveal="scale" data-delay="0">
                <div class="lp-feature-accent ac-t"></div>
                <div class="lp-feature-body">
                    <div class="lp-fi fi-t"><i class="bi bi-credit-card-2-front"></i></div>
                    <h3>Online payments</h3>
                    <p>Pay service fees directly through the platform — credit card and cryptocurrency supported.</p>
                </div>
            </div>
            <div class="lp-feature" data-reveal="scale" data-delay="50">
                <div class="lp-feature-accent ac-s"></div>
                <div class="lp-feature-body">
                    <div class="lp-fi fi-s"><i class="bi bi-qr-code-scan"></i></div>
                    <h3>QR code tracking</h3>
                    <p>Every request gets a unique QR code for instant status lookup. No login required.</p>
                </div>
            </div>
            <div class="lp-feature" data-reveal="scale" data-delay="100">
                <div class="lp-feature-accent ac-a"></div>
                <div class="lp-feature-body">
                    <div class="lp-fi fi-a"><i class="bi bi-calendar-check"></i></div>
                    <h3>Appointment booking</h3>
                    <p>Book and manage appointments tied to your service requests with reminders.</p>
                </div>
            </div>
            <div class="lp-feature" data-reveal="scale" data-delay="150">
                <div class="lp-feature-accent ac-v"></div>
                <div class="lp-feature-body">
                    <div class="lp-fi fi-v"><i class="bi bi-bell"></i></div>
                    <h3>Real-time notifications</h3>
                    <p>Get alerted on status changes, document requests, and payment deadlines instantly.</p>
                </div>
            </div>
            <div class="lp-feature" data-reveal="scale" data-delay="200">
                <div class="lp-feature-accent ac-e"></div>
                <div class="lp-feature-body">
                    <div class="lp-fi fi-e"><i class="bi bi-file-earmark-arrow-up"></i></div>
                    <h3>Document uploads</h3>
                    <p>Attach supporting documents digitally. Offices can request additional files as needed.</p>
                </div>
            </div>
            <div class="lp-feature" data-reveal="scale" data-delay="250">
                <div class="lp-feature-accent ac-r"></div>
                <div class="lp-feature-body">
                    <div class="lp-fi fi-r"><i class="bi bi-currency-exchange"></i></div>
                    <h3>Multi-currency fees</h3>
                    <p>View service fees in USD, LBP, and EUR with live exchange rates automatically.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── HOW IT WORKS ──────────────────────────────── --}}
<section class="lp-section lp-steps-section">
    <div class="lp-section-inner">
        <div class="lp-steps-layout">
            <div data-reveal="left">
                <span class="lp-tag"><i class="bi bi-signpost-split"></i> How it works</span>
                <h2 class="lp-section-title">Simple, predictable process</h2>
                <p class="lp-section-desc" style="margin-bottom:0;">From registration to completion, every step is transparent and trackable.</p>
            </div>
            <div class="lp-steps-list">
                <div class="lp-step" data-reveal="right" data-delay="0">
                    <span class="lp-step-n">1</span>
                    <div>
                        <h4>Register and verify your profile</h4>
                        <p>Create an account with email, Google, or GitHub. Enable two-factor authentication for added security.</p>
                    </div>
                </div>
                <div class="lp-step" data-reveal="right" data-delay="60">
                    <span class="lp-step-n">2</span>
                    <div>
                        <h4>Select a municipality and submit a request</h4>
                        <p>Browse offices, review available services, and submit with any required supporting documents.</p>
                    </div>
                </div>
                <div class="lp-step" data-reveal="right" data-delay="120">
                    <span class="lp-step-n">3</span>
                    <div>
                        <h4>Track progress and pay fees</h4>
                        <p>Monitor your request in real time. Pay online via card or cryptocurrency when ready.</p>
                    </div>
                </div>
                <div class="lp-step" data-reveal="right" data-delay="180">
                    <span class="lp-step-n">4</span>
                    <div>
                        <h4>Receive your result</h4>
                        <p>Get your outcome digitally, download receipts and certificates, or attend a scheduled appointment.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── BUILT FOR EVERY ROLE ──────────────────────── --}}
<section class="lp-section lp-parallax-section" style="background: var(--cream-light); border-top:1px solid #EAE6DF; border-bottom:1px solid #EAE6DF;">
    <div class="lp-section-depth">
        <div class="lp-depth-orb lp-depth-orb--purple" data-parallax-speed="0.03"></div>
        <div class="lp-depth-orb lp-depth-orb--teal" style="top:auto;bottom:20%;left:auto;right:5%;" data-parallax-speed="0.02"></div>
    </div>
    <div class="lp-section-inner">
        <div style="text-align:center; margin-bottom:2.75rem;" data-reveal="scale">
            <span class="lp-tag"><i class="bi bi-people"></i> Multi-role platform</span>
            <h2 class="lp-section-title">Designed for every user in the process</h2>
            <p class="lp-section-desc" style="margin:0 auto;">Each role gets a tailored dashboard with tools specific to their tasks.</p>
        </div>

        <div class="lp-grid-roles">
            <div class="lp-role" data-reveal="scale" data-delay="0">
                <div class="lp-role-header rh-citizen">
                    <div class="lp-role-icon fi-t"><i class="bi bi-person"></i></div>
                    <h3>Citizens</h3>
                    <p class="lp-role-subtitle">Request, pay, and track services</p>
                </div>
                <div class="lp-role-list">
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i>Submit and track service requests</li>
                        <li><i class="bi bi-check-circle-fill"></i>Pay fees and download receipts</li>
                        <li><i class="bi bi-check-circle-fill"></i>Book appointments with offices</li>
                        <li><i class="bi bi-check-circle-fill"></i>Receive real-time notifications</li>
                        <li><i class="bi bi-check-circle-fill"></i>Track via QR code — no login needed</li>
                    </ul>
                </div>
            </div>
            <div class="lp-role" data-reveal="scale" data-delay="90">
                <div class="lp-role-header rh-office">
                    <div class="lp-role-icon fi-s"><i class="bi bi-buildings"></i></div>
                    <h3>Office Users</h3>
                    <p class="lp-role-subtitle">Process requests and manage operations</p>
                </div>
                <div class="lp-role-list">
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i>Review and process requests</li>
                        <li><i class="bi bi-check-circle-fill"></i>Manage services and fees</li>
                        <li><i class="bi bi-check-circle-fill"></i>Handle appointments and schedules</li>
                        <li><i class="bi bi-check-circle-fill"></i>Respond to citizen feedback</li>
                        <li><i class="bi bi-check-circle-fill"></i>Generate PDF reports and documents</li>
                    </ul>
                </div>
            </div>
            <div class="lp-role" data-reveal="scale" data-delay="180">
                <div class="lp-role-header rh-admin">
                    <div class="lp-role-icon fi-a"><i class="bi bi-shield-lock"></i></div>
                    <h3>Administrators</h3>
                    <p class="lp-role-subtitle">Full platform oversight and configuration</p>
                </div>
                <div class="lp-role-list">
                    <ul>
                        <li><i class="bi bi-check-circle-fill"></i>Manage municipalities and offices</li>
                        <li><i class="bi bi-check-circle-fill"></i>Oversee all platform users</li>
                        <li><i class="bi bi-check-circle-fill"></i>Monitor requests and revenue</li>
                        <li><i class="bi bi-check-circle-fill"></i>View analytics and reports</li>
                        <li><i class="bi bi-check-circle-fill"></i>Configure platform settings</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── CTA ────────────────────────────────────────── --}}
<section class="lp-section">
    <div class="lp-section-inner">
        <div class="lp-banner" data-reveal="scale">
            <div class="lp-banner-glow"><span></span><span></span><span></span></div>

            {{-- Decorative cedar silhouette --}}
            <img class="lp-banner-cedar" src="{{ asset('assets/img/brand/Lebanon cedar tree icon illustration-Photoroom.png') }}" alt="" aria-hidden="true">

            <h2>Ready to get started?</h2>
            <p>Join the platform and access municipal services from anywhere. Registration takes less than a minute.</p>
            <a href="{{ route('register') }}" class="lp-banner-btn">
                Create your free account <span class="arrow">&rarr;</span>
            </a>
        </div>
    </div>
</section>

{{-- ── FOOTER ─────────────────────────────────────── --}}
<footer class="lp-footer" data-reveal="scale">
    {{-- Gradient accent line --}}
    <div class="lp-footer-accent"></div>

    {{-- Back to top bar --}}
    <div class="lp-footer-top-bar">
        <span class="lp-footer-top-bar-label">CedarGov &mdash; Lebanon</span>
        <a href="#" class="lp-back-top" id="backToTop">
            Back to top <i class="bi bi-arrow-up-short"></i>
        </a>
    </div>

    <div class="lp-footer-inner">
        {{-- Main content --}}
        <div class="lp-footer-main">
            <div>
                <a href="{{ route('home') }}" class="lp-brand" style="color:#fff;">
                    <span class="lp-brand-mark">
                        <img src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}" alt="CedarGov icon">
                    </span>
                    <div class="lp-brand-text">
                        <strong style="color:#fff;">CedarGov</strong>
                        <span style="color:rgba(255,255,255,0.4);">Lebanon Gov Portal</span>
                    </div>
                </a>
                <p class="lp-footer-brand-text">
                    A digital platform connecting Lebanese citizens with municipal services. Fast, transparent, and accessible from anywhere.
                </p>
                <div class="lp-footer-contact">
                    <div class="lp-footer-contact-item">
                        <i class="bi bi-envelope"></i>
                        <span>support@cedargov.lb</span>
                    </div>
                    <div class="lp-footer-contact-item">
                        <i class="bi bi-geo-alt"></i>
                        <span>Beirut, Lebanon</span>
                    </div>
                    <div class="lp-footer-contact-item">
                        <i class="bi bi-clock"></i>
                        <span>Platform available 24/7</span>
                    </div>
                </div>
            </div>
            <div class="lp-footer-links">
                <div class="lp-footer-col">
                    <h4>Platform</h4>
                    <a href="{{ route('login') }}">Sign in</a>
                    <a href="{{ route('register') }}">Create account</a>
                    <a href="#">Browse services</a>
                    <a href="#">Track request</a>
                </div>
                <div class="lp-footer-col">
                    <h4>Resources</h4>
                    <a href="#">Documentation</a>
                    <a href="#">API access</a>
                    <a href="#">Support center</a>
                    <a href="#">Status page</a>
                </div>
                <div class="lp-footer-col">
                    <h4>Legal</h4>
                    <a href="#">Privacy policy</a>
                    <a href="#">Terms of service</a>
                    <a href="#">Cookie policy</a>
                    <a href="#">Accessibility</a>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="lp-footer-divider"></div>

        {{-- Bottom bar --}}
        <div class="lp-footer-bottom">
            <div class="lp-footer-bottom-left">
                <img src="{{ asset('assets/img/brand/cedar-logo-icon-trim.png') }}" alt="CedarGov">
                <span>CedarGov &mdash; Lebanese Municipalities &copy; {{ now()->year }}</span>
            </div>
            <div class="lp-footer-socials">
                <a href="#" aria-label="GitHub"><i class="bi bi-github"></i></a>
                <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
            </div>
        </div>
    </div>
</footer>

<script>
(function () {
    var rm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!rm) document.documentElement.classList.add('js-motion');

    /* ── Scroll progress bar ──────────────── */
    var progBar = document.getElementById('scrollProgress');

    /* ── Nav scroll ───────────────────────── */
    var nav = document.getElementById('lpNav');
    function onScroll() {
        if (nav) nav.classList.toggle('scrolled', window.scrollY > 10);
        /* Update scroll progress */
        if (progBar) {
            var h = document.documentElement.scrollHeight - window.innerHeight;
            var pct = h > 0 ? (window.scrollY / h) * 100 : 0;
            progBar.style.width = pct + '%';
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ── Reveal on scroll ─────────────────── */
    var els = document.querySelectorAll('[data-reveal]');
    if (rm) { els.forEach(function(el) { el.classList.add('is-visible'); }); }
    else {
        var obs = new IntersectionObserver(function(entries, o) {
            entries.forEach(function(e) {
                if (!e.isIntersecting) return;
                var d = parseInt(e.target.dataset.delay || '0', 10);
                if (d > 0) e.target.style.setProperty('--rv', d + 'ms');
                e.target.classList.add('is-visible');
                o.unobserve(e.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
        els.forEach(function(el) { obs.observe(el); });
    }

    /* ── Hero headline stagger reveal ─────── */
    var headline = document.getElementById('heroHeadline');
    if (headline && !rm) {
        var words = headline.querySelectorAll('.word');
        setTimeout(function() {
            headline.classList.add('text-revealed');
            words.forEach(function(w, i) {
                w.style.transitionDelay = (i * 150 + 200) + 'ms';
            });
        }, 300);
    } else if (headline) {
        headline.classList.add('text-revealed');
        headline.querySelectorAll('.word').forEach(function(w) {
            w.style.opacity = '1';
            w.style.transform = 'none';
        });
    }

    /* ── Parallax on hero blobs ────────────── */
    if (!rm && window.innerWidth > 768) {
        var blobs = document.querySelectorAll('.lp-hero-blob');
        var shapes = document.querySelectorAll('.lp-shape');
        window.addEventListener('scroll', function() {
            var sy = window.scrollY;
            if (sy > window.innerHeight) return; /* stop when past hero */
            blobs.forEach(function(b, i) {
                var speed = 0.03 + (i * 0.015);
                b.style.transform = 'translateY(' + (sy * speed) + 'px)';
            });
            shapes.forEach(function(s, i) {
                var speed = 0.02 + (i * 0.008);
                s.style.marginTop = (sy * speed) + 'px';
            });
        }, { passive: true });
    }

    /* ── Section parallax depth ──────────── */
    var pSections = document.querySelectorAll('.lp-parallax-section');
    if (!rm && pSections.length) {
        var depthObs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
                e.target.classList.toggle('depth-visible', e.isIntersecting);
            });
        }, { threshold: 0.05 });
        pSections.forEach(function(s) { depthObs.observe(s); });

        window.addEventListener('scroll', function() {
            pSections.forEach(function(sec) {
                if (!sec.classList.contains('depth-visible')) return;
                var rect = sec.getBoundingClientRect();
                var center = rect.top + rect.height / 2 - window.innerHeight / 2;
                var orbs = sec.querySelectorAll('[data-parallax-speed]');
                orbs.forEach(function(orb) {
                    var speed = parseFloat(orb.dataset.parallaxSpeed) || 0.03;
                    orb.style.transform = 'translateY(' + (center * speed * -1) + 'px)';
                });
            });
        }, { passive: true });
    }

    /* ── 3D card tilt ─────────────────────── */
    var cw = document.getElementById('cardWrap');
    var tc = document.getElementById('tiltCard');
    if (cw && tc && !rm && window.innerWidth > 1023) {
        cw.addEventListener('mousemove', function(e) {
            var r = cw.getBoundingClientRect();
            var x = e.clientX - r.left, y = e.clientY - r.top;
            var rx = ((y - r.height/2) / (r.height/2)) * -7;
            var ry = ((x - r.width/2) / (r.width/2)) * 7;
            tc.style.transform = 'rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
            var sh = tc.querySelector('.lp-card-shine');
            if (sh) {
                sh.style.setProperty('--mx', (x/r.width*100)+'%');
                sh.style.setProperty('--my', (y/r.height*100)+'%');
            }
        });
        cw.addEventListener('mouseleave', function() {
            tc.style.transition = 'transform 0.5s cubic-bezier(0.22,1,0.36,1)';
            tc.style.transform = 'rotateX(0) rotateY(0)';
            setTimeout(function() { tc.style.transition = 'transform 0.1s ease-out'; }, 500);
        });
        cw.addEventListener('mouseenter', function() { tc.style.transition = 'transform 0.1s ease-out'; });
    }

    /* ── Counter animation with glow ──────── */
    var ctrs = document.querySelectorAll('[data-counter]');
    function fmt(v, d) { return v.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d }); }
    function animCounter(el) {
        if (el.dataset.counted) return;
        var t = parseFloat(el.dataset.counter);
        if (isNaN(t)) return;
        var dec = Number.isInteger(t) ? 0 : 1;
        if (rm || t === 0) { el.textContent = fmt(t, dec); el.dataset.counted = '1'; return; }
        el.classList.add('is-counting');
        var dur = 1400, st = performance.now();
        function tick(now) {
            var p = Math.min((now - st) / dur, 1);
            var e = 1 - Math.pow(1 - p, 3);
            el.textContent = fmt(t * e, dec);
            if (p < 1) requestAnimationFrame(tick);
            else {
                el.textContent = fmt(t, dec);
                el.dataset.counted = '1';
                setTimeout(function() { el.classList.remove('is-counting'); }, 400);
            }
        }
        requestAnimationFrame(tick);
    }
    if (ctrs.length) {
        if (rm) ctrs.forEach(animCounter);
        else {
            var co = new IntersectionObserver(function(entries, o) {
                entries.forEach(function(e) {
                    if (!e.isIntersecting) return;
                    animCounter(e.target); o.unobserve(e.target);
                });
            }, { threshold: 0.4 });
            ctrs.forEach(function(c) { co.observe(c); });
        }
    }

    /* ── Steps scroll-linked progress ─────── */
    var stepsList = document.querySelector('.lp-steps-list');
    if (stepsList && !rm) {
        var steps = stepsList.querySelectorAll('.lp-step');
        function updateStepProgress() {
            var rect = stepsList.getBoundingClientRect();
            var listTop = rect.top;
            var listH = rect.height;
            if (listH <= 0) return;
            /* Progress: 0 when top hits 70% viewport, 1 when bottom hits 40% viewport */
            var triggerTop = window.innerHeight * 0.7;
            var triggerBot = window.innerHeight * 0.4;
            var progress = (triggerTop - listTop) / (listH + triggerTop - triggerBot);
            progress = Math.max(0, Math.min(1, progress));
            stepsList.style.setProperty('--line-progress', progress);
            /* Light up each step as line reaches it */
            steps.forEach(function(step, i) {
                var threshold = (i + 0.5) / steps.length;
                step.classList.toggle('step-reached', progress >= threshold);
            });
        }
        window.addEventListener('scroll', updateStepProgress, { passive: true });
        updateStepProgress();
    } else if (stepsList) {
        stepsList.style.setProperty('--line-progress', '1');
        stepsList.querySelectorAll('.lp-step').forEach(function(s) { s.classList.add('step-reached'); });
    }

    /* ── Back to top ──────────────────────── */
    var btt = document.getElementById('backToTop');
    if (btt) {
        btt.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* ── Smooth anchor scroll ─────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function(a) {
        if (a.id === 'backToTop') return;
        a.addEventListener('click', function(e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

})();
</script>

</body>
</html>
