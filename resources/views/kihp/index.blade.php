<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>{{ $school->name }} — Every Life Counts | Kampala, Uganda</title>
    <meta name="description" content="KIHP — Kampala Institute of Health Professionals. Accredited certificate and diploma programmes in Clinical Medicine, Pharmacy, Medical Lab, Public Health and more. Based in Kampala, Uganda.">
    <meta name="keywords" content="KIHP, Kampala Institute of Health Professionals, Clinical Medicine, Pharmacy, Medical Laboratory, Public Health, Uganda health training">
    <meta name="author" content="SchoolDynamics.ug">
    <meta name="robots" content="index, follow">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/kihp') }}">
    <meta property="og:title" content="{{ $school->name }} — Every Life Counts">
    <meta property="og:description" content="Accredited health professional training programmes in Kampala, Uganda. {{ $studentCount }}+ enrolled students.">
    <meta property="og:image" content="{{ url('storage/' . $school->logo) }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $school->name }}">
    <meta name="twitter:description" content="Accredited health professional training — Kampala, Uganda.">
    <meta name="twitter:image" content="{{ url('storage/' . $school->logo) }}">

    <link rel="canonical" href="{{ url('/kihp') }}">
    <link rel="shortcut icon" href="{{ url('storage/' . $school->logo) }}" type="image/jpeg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">

    <style>
        /* ── Reset & Base ─────────────────────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --cyan:        #00beff;
            --cyan-dk:     #009fd9;
            --cyan-lt:     #33cbff;
            --cyan-tint:   #e6f9ff;
            --navy:        #061325;
            --navy-md:     #0b2040;
            --navy-lt:     #153259;
            --gold:        #f4a71d;
            --gold-dk:     #c8880e;
            --green:       #10b981;
            --text:        #0f172a;
            --body:        #374151;
            --muted:       #6b7280;
            --border:      #e5e7eb;
            --surface:     #f0faff;
            --white:       #ffffff;
            --radius-sm:   6px;
            --radius:      12px;
            --radius-lg:   20px;
            --shadow-sm:   0 1px 4px rgba(0,0,0,.06);
            --shadow:      0 4px 20px rgba(0,0,0,.09);
            --shadow-lg:   0 12px 40px rgba(0,0,0,.13);
        }

        html { scroll-behavior: smooth; font-size: 16px; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--body); background: var(--white);
            line-height: 1.65; -webkit-font-smoothing: antialiased;
        }
        img { max-width: 100%; height: auto; display: block; }
        a { color: inherit; }

        /* ── Utilities ────────────────────────────────────────────── */
        .container {
            max-width: 1180px; margin-left: auto; margin-right: auto;
            padding-left: clamp(20px,5vw,48px); padding-right: clamp(20px,5vw,48px);
        }
        .section { padding: clamp(64px,8vw,100px) 0; }

        .eyebrow {
            display: inline-block; font-size: .72rem; font-weight: 700;
            letter-spacing: .1em; text-transform: uppercase;
            color: var(--cyan); margin-bottom: 10px;
        }
        .section-title {
            font-size: clamp(1.7rem,3.5vw,2.6rem); font-weight: 800;
            color: var(--text); line-height: 1.15; letter-spacing: -.03em; margin-bottom: 14px;
        }
        .section-lead {
            font-size: 1.05rem; color: var(--muted); max-width: 560px; line-height: 1.75;
        }
        .section-header { margin-bottom: 52px; }

        /* ── Navbar ──────────────────────────────────────────────── */
        .nav {
            position: fixed; inset-inline: 0; top: 0; z-index: 900;
            height: 66px; display: flex; align-items: center;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(14px) saturate(180%);
            -webkit-backdrop-filter: blur(14px) saturate(180%);
            border-bottom: 1px solid transparent;
            transition: border-color .3s, box-shadow .3s;
        }
        .nav.scrolled {
            border-color: var(--border);
            box-shadow: 0 2px 16px rgba(0,190,255,.08);
        }
        .nav-inner { display: flex; align-items: center; justify-content: space-between; gap: 24px; width: 100%; }

        .nav-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; flex-shrink: 0; }
        .nav-brand img { height: 38px; width: auto; border-radius: 6px; object-fit: contain; background: #fff; }
        .nav-brand-name { font-size: .9rem; font-weight: 800; color: var(--navy); line-height: 1.2; }
        .nav-brand-sub { font-size: .62rem; font-weight: 500; color: var(--muted); letter-spacing: .05em; text-transform: uppercase; }

        .nav-links { display: flex; align-items: center; gap: 4px; list-style: none; }
        .nav-links a {
            font-size: .85rem; font-weight: 500; color: var(--body);
            padding: 8px 12px; border-radius: var(--radius-sm); text-decoration: none;
            transition: color .2s, background .2s;
        }
        .nav-links a:hover { color: var(--cyan); background: var(--cyan-tint); }

        .btn-nav {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px;
            background: var(--cyan); color: var(--white);
            font-size: .85rem; font-weight: 600; text-decoration: none; white-space: nowrap;
            border-radius: var(--radius-sm); transition: background .2s, box-shadow .2s;
            box-shadow: 0 2px 10px rgba(0,190,255,.3);
        }
        .btn-nav:hover { background: var(--cyan-dk); box-shadow: 0 4px 18px rgba(0,190,255,.4); }

        .nav-hamburger {
            display: none; flex-direction: column; gap: 5px; cursor: pointer; padding: 6px;
            background: none; border: none;
        }
        .nav-hamburger span { display: block; width: 22px; height: 2px; background: var(--text); border-radius: 2px; transition: all .3s; }

        /* ── Mobile Nav ──────────────────────────────────────────── */
        .mobile-nav {
            display: none; position: fixed; inset: 0; z-index: 800;
            background: rgba(6,19,37,.97);
            flex-direction: column; align-items: center; justify-content: center; gap: 10px;
        }
        .mobile-nav.open { display: flex; }
        .mobile-nav a {
            font-size: 1.2rem; font-weight: 600; color: var(--white);
            text-decoration: none; padding: 14px 32px; border-radius: var(--radius-sm);
            transition: background .2s;
        }
        .mobile-nav a:hover { background: rgba(255,255,255,.08); }
        .mobile-nav-close {
            position: absolute; top: 20px; right: 24px;
            background: none; border: none; color: rgba(255,255,255,.6); font-size: 1.5rem; cursor: pointer;
        }

        /* ── Hero ────────────────────────────────────────────────── */
        .hero {
            position: relative; min-height: 100svh;
            display: flex; align-items: center; overflow: hidden; padding-top: 66px;
        }
        .hero-bg {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-md) 50%, #0d3060 100%);
            z-index: 0;
        }
        .hero-bg::before {
            content: '';
            position: absolute; inset: 0; z-index: 1;
            background-image: radial-gradient(circle, rgba(0,190,255,.12) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .hero-bg::after {
            content: '';
            position: absolute; top: -200px; right: -200px;
            width: 700px; height: 700px; border-radius: 50%;
            background: radial-gradient(circle, rgba(0,190,255,.18) 0%, transparent 70%);
            z-index: 0;
        }
        .hero-pulse {
            position: absolute; bottom: -150px; left: -150px;
            width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(0,190,255,.1) 0%, transparent 70%);
            z-index: 0;
        }
        .hero-inner {
            position: relative; z-index: 2;
            padding: clamp(60px,10vw,120px) 0 clamp(60px,8vw,100px);
            display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px;
            background: rgba(0,190,255,.15); border: 1px solid rgba(0,190,255,.4);
            border-radius: 100px; font-size: .72rem; font-weight: 700;
            color: var(--cyan); letter-spacing: .07em; text-transform: uppercase;
            margin-bottom: 22px;
        }
        .hero-badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--cyan); animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .5; transform: scale(1.3); }
        }
        .hero-title {
            font-size: clamp(2.1rem,4.5vw,3.4rem); font-weight: 900;
            color: var(--white); line-height: 1.1; letter-spacing: -.04em; margin-bottom: 22px;
        }
        .hero-title span { color: var(--cyan); }
        .hero-title em { color: var(--gold); font-style: normal; }
        .hero-desc {
            font-size: 1.05rem; color: rgba(255,255,255,.75); line-height: 1.8;
            max-width: 500px; margin-bottom: 38px;
        }
        .hero-actions { display: flex; gap: 14px; flex-wrap: wrap; }

        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 15px 32px; background: var(--cyan); color: var(--navy);
            font-size: .97rem; font-weight: 800; text-decoration: none; border-radius: var(--radius-sm);
            box-shadow: 0 8px 28px rgba(0,190,255,.35);
            transition: transform .2s, box-shadow .2s, background .2s;
        }
        .btn-hero-primary:hover { background: var(--cyan-lt); transform: translateY(-2px); box-shadow: 0 12px 36px rgba(0,190,255,.45); }

        .btn-hero-outline {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 14px 26px; border: 2px solid rgba(255,255,255,.35);
            color: var(--white); font-size: .9rem; font-weight: 600;
            text-decoration: none; border-radius: var(--radius-sm);
            transition: background .2s, border-color .2s;
        }
        .btn-hero-outline:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.7); }

        /* Hero stats */
        .hero-stats {
            display: grid; grid-template-columns: repeat(3,1fr); gap: 0; margin-top: 48px;
            border: 1px solid rgba(255,255,255,.15); border-radius: var(--radius); overflow: hidden;
            background: rgba(255,255,255,.05); backdrop-filter: blur(8px);
        }
        .hero-stat { padding: 22px 18px; text-align: center; }
        .hero-stat + .hero-stat { border-left: 1px solid rgba(255,255,255,.1); }
        .hero-stat-val { font-size: 2rem; font-weight: 900; color: var(--white); line-height: 1; }
        .hero-stat-val span { color: var(--cyan); }
        .hero-stat-lbl { font-size: .68rem; color: rgba(255,255,255,.55); text-transform: uppercase; letter-spacing: .07em; margin-top: 5px; }

        /* Hero right: floating info cards */
        .hero-cards { display: flex; flex-direction: column; gap: 14px; }
        .hero-card {
            background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.14);
            backdrop-filter: blur(12px); border-radius: var(--radius);
            padding: 18px 22px; color: var(--white);
            display: flex; align-items: center; gap: 16px;
        }
        .hero-card-icon {
            width: 46px; height: 46px; flex-shrink: 0; border-radius: 12px;
            background: rgba(0,190,255,.2); border: 1px solid rgba(0,190,255,.3);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; color: var(--cyan);
        }
        .hero-card-name { font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; opacity: .6; }
        .hero-card-val  { font-size: 1rem; font-weight: 700; margin-top: 3px; }

        /* ── Section: About ──────────────────────────────────────── */
        .about-section { background: var(--surface); }
        .about-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center; }
        .about-logo-wrap {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-md) 100%);
            border-radius: var(--radius-lg); padding: 48px; text-align: center;
            position: relative; overflow: hidden;
        }
        .about-logo-wrap::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(0,190,255,.1) 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .about-logo-wrap img {
            max-height: 120px; max-width: 200px; margin: 0 auto;
            position: relative; z-index: 1; filter: drop-shadow(0 4px 16px rgba(0,0,0,.4));
        }
        .about-logo-motto {
            margin-top: 24px; position: relative; z-index: 1;
            font-size: 1.1rem; font-weight: 800; color: var(--cyan);
            letter-spacing: .03em;
        }
        .about-logo-motto em { display: block; font-style: normal; font-size: .72rem; font-weight: 500; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 4px; }
        .about-meta { display: flex; flex-direction: column; gap: 10px; margin-top: 24px; }
        .about-meta-row { display: flex; align-items: center; gap: 10px; font-size: .85rem; color: var(--muted); }
        .about-meta-row i { color: var(--cyan); width: 16px; flex-shrink: 0; }
        .about-meta-row a { color: var(--cyan); text-decoration: none; }
        .about-meta-row a:hover { text-decoration: underline; }

        /* ── Section: Programs ───────────────────────────────────── */
        .programs-section { background: var(--white); }
        .programs-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; }
        .program-card {
            padding: 24px 22px 26px;
            border: 1.5px solid var(--border); border-radius: var(--radius);
            background: var(--white); position: relative; overflow: hidden;
            transition: border-color .25s, box-shadow .25s, transform .25s;
        }
        .program-card:hover {
            border-color: var(--cyan); box-shadow: 0 8px 32px rgba(0,190,255,.12);
            transform: translateY(-4px);
        }
        .program-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        }
        .program-card.cert::before  { background: linear-gradient(90deg, var(--cyan), var(--cyan-lt)); }
        .program-card.dip::before   { background: linear-gradient(90deg, var(--navy-lt), var(--cyan)); }
        .program-card.online::before { background: linear-gradient(90deg, var(--gold), var(--gold-dk)); }

        .program-badge {
            display: inline-block; font-size: .65rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em;
            padding: 3px 10px; border-radius: 100px; margin-bottom: 12px;
        }
        .program-badge.cert   { background: var(--cyan-tint); color: var(--cyan-dk); }
        .program-badge.dip    { background: #e0f0ff; color: var(--navy-lt); }
        .program-badge.online { background: #fff8e6; color: var(--gold-dk); }

        .program-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; margin-bottom: 14px;
        }
        .program-card h3 { font-size: .95rem; font-weight: 700; color: var(--text); line-height: 1.35; margin-bottom: 8px; }
        .program-card p  { font-size: .82rem; color: var(--muted); line-height: 1.65; }
        .program-arrow {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .78rem; font-weight: 600; color: var(--cyan); margin-top: 14px;
            text-decoration: none;
        }
        .program-arrow i { font-size: .7rem; transition: transform .2s; }
        .program-arrow:hover i { transform: translateX(3px); }

        /* ── Section: Features ───────────────────────────────────── */
        .features-section { background: var(--surface); }
        .features-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 22px; }
        .feature-card {
            padding: 28px 26px 30px;
            border: 1px solid var(--border); border-radius: var(--radius);
            background: var(--white);
            transition: border-color .25s, box-shadow .25s, transform .25s;
        }
        .feature-card:hover { border-color: var(--cyan); box-shadow: 0 8px 32px rgba(0,190,255,.1); transform: translateY(-4px); }
        .fi { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 18px; }
        .feature-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; color: var(--text); }
        .feature-card p  { font-size: .875rem; color: var(--muted); line-height: 1.7; }

        /* ── Section: Stats ──────────────────────────────────────── */
        .stats-section {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-md) 60%, #0d3060 100%);
            position: relative; overflow: hidden;
        }
        .stats-section::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(0,190,255,.08) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .stats-grid { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(4,1fr); gap: 0; border: 1px solid rgba(255,255,255,.1); border-radius: var(--radius-lg); overflow: hidden; }
        .stat-box { padding: 44px 28px; text-align: center; }
        .stat-box + .stat-box { border-left: 1px solid rgba(255,255,255,.08); }
        .stat-box-icon { font-size: 2rem; color: var(--cyan); opacity: .6; margin-bottom: 14px; }
        .stat-box-val { font-size: 3rem; font-weight: 900; color: var(--white); line-height: 1; letter-spacing: -.04em; }
        .stat-box-val span { color: var(--cyan); }
        .stat-box-lbl { font-size: .75rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .1em; margin-top: 8px; }

        /* ── Section: Admissions ─────────────────────────────────── */
        .admission-section { background: var(--white); }
        .admission-inner {
            display: grid; grid-template-columns: 1.2fr 1fr; gap: 64px; align-items: center;
        }
        .admission-steps { display: flex; flex-direction: column; gap: 18px; margin-top: 28px; }
        .admission-step {
            display: flex; align-items: flex-start; gap: 16px;
            padding: 18px 20px;
            border: 1px solid var(--border); border-radius: var(--radius);
            background: var(--surface);
        }
        .step-num {
            width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%;
            background: var(--cyan); color: var(--navy);
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; font-weight: 900;
        }
        .step-text h4 { font-size: .9rem; font-weight: 700; color: var(--text); margin-bottom: 3px; }
        .step-text p  { font-size: .82rem; color: var(--muted); line-height: 1.6; }

        .admission-cta-box {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-md) 100%);
            border-radius: var(--radius-lg); padding: 44px 36px; text-align: center;
            position: relative; overflow: hidden;
        }
        .admission-cta-box::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(0,190,255,.1) 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .admission-cta-box > * { position: relative; z-index: 1; }
        .admission-cta-box h3 { font-size: 1.5rem; font-weight: 800; color: var(--white); margin-bottom: 10px; }
        .admission-cta-box p  { font-size: .9rem; color: rgba(255,255,255,.65); margin-bottom: 28px; line-height: 1.7; }

        .btn-apply-primary {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 15px 32px; background: var(--cyan); color: var(--navy);
            font-size: 1rem; font-weight: 800; text-decoration: none; border-radius: var(--radius-sm);
            box-shadow: 0 8px 28px rgba(0,190,255,.35);
            transition: transform .2s, box-shadow .2s;
            width: 100%; justify-content: center; margin-bottom: 12px;
        }
        .btn-apply-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 36px rgba(0,190,255,.45); }

        .btn-apply-outline {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 13px 24px; border: 2px solid rgba(255,255,255,.25);
            color: rgba(255,255,255,.8); font-size: .9rem; font-weight: 600;
            text-decoration: none; border-radius: var(--radius-sm); width: 100%; justify-content: center;
            transition: background .2s, border-color .2s;
        }
        .btn-apply-outline:hover { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.6); }

        /* ── Section: Contact ────────────────────────────────────── */
        .contact-section { background: var(--surface); }
        .contact-inner { display: grid; grid-template-columns: 1fr 1fr; gap: 52px; align-items: start; }
        .contact-cards { display: flex; flex-direction: column; gap: 16px; }
        .contact-card {
            display: flex; align-items: flex-start; gap: 16px;
            padding: 20px 22px; background: var(--white);
            border: 1px solid var(--border); border-radius: var(--radius);
            transition: border-color .2s, box-shadow .2s;
        }
        .contact-card:hover { border-color: var(--cyan); box-shadow: var(--shadow-sm); }
        .contact-icon {
            width: 42px; height: 42px; flex-shrink: 0; border-radius: 10px;
            background: var(--cyan-tint); color: var(--cyan);
            display: flex; align-items: center; justify-content: center; font-size: 1rem;
        }
        .contact-card h4 { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: 4px; }
        .contact-card p  { font-size: .9rem; color: var(--text); line-height: 1.6; }
        .contact-card a  { color: var(--cyan); text-decoration: none; }
        .contact-card a:hover { text-decoration: underline; }

        .contact-map-placeholder {
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy-md) 100%);
            border-radius: var(--radius-lg); overflow: hidden; position: relative;
            min-height: 320px; display: flex; flex-direction: column; align-items: center; justify-content: center;
            text-align: center;
        }
        .contact-map-placeholder::before {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(circle, rgba(0,190,255,.1) 1px, transparent 1px);
            background-size: 22px 22px;
        }
        .contact-map-placeholder > * { position: relative; z-index: 1; }
        .contact-map-placeholder i { font-size: 3rem; color: var(--cyan); opacity: .6; margin-bottom: 16px; }
        .contact-map-placeholder h4 { font-size: 1.1rem; font-weight: 800; color: var(--white); margin-bottom: 8px; }
        .contact-map-placeholder p { font-size: .85rem; color: rgba(255,255,255,.55); max-width: 260px; line-height: 1.7; }
        .contact-map-btn {
            display: inline-flex; align-items: center; gap: 8px; margin-top: 20px;
            padding: 12px 24px; background: var(--cyan); color: var(--navy);
            font-size: .85rem; font-weight: 700; text-decoration: none; border-radius: var(--radius-sm);
            transition: background .2s;
        }
        .contact-map-btn:hover { background: var(--cyan-lt); }

        /* ── CTA Strip ───────────────────────────────────────────── */
        .cta-strip { background: var(--cyan-tint); padding: clamp(56px,7vw,88px) 0; text-align: center; }
        .cta-strip h2 { font-size: clamp(1.7rem,3vw,2.4rem); font-weight: 800; color: var(--navy); letter-spacing: -.03em; margin-bottom: 14px; }
        .cta-strip p  { font-size: 1rem; color: var(--muted); max-width: 460px; margin: 0 auto 36px; }
        .btn-cta {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 16px 36px; background: var(--navy); color: var(--white);
            font-size: 1rem; font-weight: 700; text-decoration: none; border-radius: var(--radius-sm);
            box-shadow: 0 8px 28px rgba(6,19,37,.25);
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .btn-cta:hover { background: var(--navy-md); transform: translateY(-2px); box-shadow: 0 12px 36px rgba(6,19,37,.35); }
        .btn-cta-cyan {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 16px 36px; background: var(--cyan); color: var(--navy);
            font-size: 1rem; font-weight: 700; text-decoration: none; border-radius: var(--radius-sm);
            box-shadow: 0 8px 28px rgba(0,190,255,.3);
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .btn-cta-cyan:hover { background: var(--cyan-lt); transform: translateY(-2px); }

        /* ── Footer ──────────────────────────────────────────────── */
        .footer { background: var(--navy); color: rgba(255,255,255,.5); padding: 60px 0 0; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 56px; padding-bottom: 48px; }
        .footer-brand { display: flex; gap: 14px; align-items: center; margin-bottom: 16px; }
        .footer-brand img { height: 40px; width: auto; flex-shrink: 0; border-radius: 6px; background: #fff; padding: 4px; }
        .footer-brand-t strong { display: block; font-size: .9rem; font-weight: 800; color: #fff; }
        .footer-brand-t span   { font-size: .72rem; color: rgba(255,255,255,.4); }
        .footer-about { font-size: .82rem; line-height: 1.75; max-width: 380px; margin-bottom: 18px; }
        .footer-motto {
            font-size: .8rem; font-weight: 700; color: var(--cyan);
            padding: 10px 14px; background: rgba(0,190,255,.08);
            border: 1px solid rgba(0,190,255,.2); border-radius: var(--radius-sm);
            display: inline-block;
        }
        .footer-col h4 {
            font-size: .72rem; font-weight: 700; color: rgba(255,255,255,.35);
            text-transform: uppercase; letter-spacing: .1em; margin-bottom: 18px;
        }
        .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 11px; }
        .footer-col ul li { font-size: .82rem; }
        .footer-col ul li a { color: rgba(255,255,255,.5); text-decoration: none; transition: color .2s; }
        .footer-col ul li a:hover { color: var(--cyan); }
        .footer-col ul li span { color: rgba(255,255,255,.35); }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,.07); padding: 22px 0;
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 10px; font-size: .77rem;
        }
        .footer-bottom a { color: rgba(255,255,255,.3); text-decoration: none; transition: color .2s; }
        .footer-bottom a:hover { color: rgba(255,255,255,.8); }
        .footer-sys-badge {
            display: inline-flex; align-items: center; gap: 6px; font-size: .72rem;
            padding: 4px 10px; background: rgba(0,190,255,.1); border: 1px solid rgba(0,190,255,.2);
            border-radius: 100px; color: var(--cyan);
        }

        /* ── Fade-in (Intersection Observer) ─────────────────────── */
        .fade-in { opacity: 0; transform: translateY(24px); transition: opacity .55s ease, transform .55s ease; }
        .fade-in.visible { opacity: 1; transform: none; }

        /* ── Responsive ──────────────────────────────────────────── */
        @media (max-width: 1024px) {
            .hero-inner     { grid-template-columns: 1fr; }
            .hero-cards     { display: none; }
            .hero-desc      { max-width: none; }
            .features-grid  { grid-template-columns: repeat(2,1fr); }
            .programs-grid  { grid-template-columns: repeat(2,1fr); }
            .stats-grid     { grid-template-columns: repeat(2,1fr); }
            .stat-box + .stat-box:nth-child(3) { border-left: none; border-top: 1px solid rgba(255,255,255,.08); }
            .stat-box + .stat-box:nth-child(4) { border-top: 1px solid rgba(255,255,255,.08); }
            .about-inner    { grid-template-columns: 1fr; }
            .admission-inner { grid-template-columns: 1fr; }
            .contact-inner  { grid-template-columns: 1fr; }
            .footer-grid    { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .nav-links  { display: none; }
            .nav-hamburger { display: flex; }
            .programs-grid  { grid-template-columns: 1fr; }
            .features-grid  { grid-template-columns: 1fr; }
            .stats-grid     { grid-template-columns: repeat(2,1fr); }
            .footer-grid    { grid-template-columns: 1fr; gap: 32px; }
            .hero-stats     { display: none; }
        }
        @media (max-width: 540px) {
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .stat-box + .stat-box { border-left: none; border-top: 1px solid rgba(255,255,255,.08); }
            .stat-box:nth-child(odd) { border-left: none; }
            .stat-box:nth-child(even) { border-left: 1px solid rgba(255,255,255,.08); }
            .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

{{-- Mobile nav overlay --}}
<div class="mobile-nav" id="mobileNav">
    <button class="mobile-nav-close" id="mobileClose" aria-label="Close menu">
        <i class="fa fa-times"></i>
    </button>
    <a href="#about"      onclick="closeMobileNav()">About</a>
    <a href="#programs"   onclick="closeMobileNav()">Programmes</a>
    <a href="#admissions" onclick="closeMobileNav()">Admissions</a>
    <a href="#contact"    onclick="closeMobileNav()">Contact</a>
    <a href="{{ url('admin/auth/login') }}" style="margin-top:16px; background:var(--cyan); color:var(--navy); padding:14px 36px; font-weight:800;">
        Student Portal
    </a>
</div>

{{-- Navbar --}}
<nav class="nav" id="mainNav" role="navigation" aria-label="Main navigation">
    <div class="container">
        <div class="nav-inner">
            <a href="{{ url('/kihp') }}" class="nav-brand" aria-label="KIHP Home">
                <img src="{{ url('storage/' . $school->logo) }}" alt="{{ $school->short_name }} Logo" onerror="this.style.display='none'">
                <div>
                    <div class="nav-brand-name">{{ $school->short_name }}</div>
                    <div class="nav-brand-sub">Kampala, Uganda</div>
                </div>
            </a>
            <ul class="nav-links" role="list">
                <li><a href="#about">About</a></li>
                <li><a href="#programs">Programmes</a></li>
                <li><a href="#why-kihp">Why KIHP</a></li>
                <li><a href="#admissions">Admissions</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <a href="{{ url('admin/auth/login') }}" class="btn-nav">
                <i class="fa fa-sign-in-alt"></i> Student Portal
            </a>
            <button class="nav-hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="hero" id="top">
    <div class="hero-bg"></div>
    <div class="hero-pulse"></div>
    <div class="container">
        <div class="hero-inner">
            <div>
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    Health Professional Training — Kampala, Uganda
                </div>
                <h1 class="hero-title">
                    <em>Every Life</em><br>
                    <span>Counts.</span><br>
                    Train With Purpose.
                </h1>
                <p class="hero-desc">
                    {{ $school->name }} is an accredited institution offering certificate and diploma programmes
                    in Clinical Medicine, Pharmacy, Medical Laboratory, Public Health and more —
                    empowering Uganda's next generation of health professionals.
                </p>
                <div class="hero-actions">
                    <a href="#admissions" class="btn-hero-primary">
                        <i class="fa fa-user-plus"></i> Apply Now
                    </a>
                    <a href="#programs" class="btn-hero-outline">
                        <i class="fa fa-book-medical"></i> View Programmes
                    </a>
                    <a href="{{ url('admin/auth/login') }}" class="btn-hero-outline" style="border-color:rgba(255,255,255,.2);opacity:.7;font-size:.82rem;padding:10px 16px;">
                        <i class="fa fa-sign-in-alt"></i> Student Login
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="hero-stat">
                        <div class="hero-stat-val"><span>{{ number_format($studentCount) }}</span>+</div>
                        <div class="hero-stat-lbl">Enrolled Students</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-val">{{ $programs->count() }}</div>
                        <div class="hero-stat-lbl">Programmes</div>
                    </div>
                    <div class="hero-stat">
                        <div class="hero-stat-val"><span><i class="fa fa-wifi" style="font-size:1.4rem"></i></span></div>
                        <div class="hero-stat-lbl">Online Available</div>
                    </div>
                </div>
            </div>

            {{-- Floating info cards (desktop) --}}
            <div class="hero-cards" aria-hidden="true">
                <div class="hero-card fade-in visible">
                    <div class="hero-card-icon"><i class="fa fa-stethoscope"></i></div>
                    <div>
                        <div class="hero-card-name">Clinical Medicine</div>
                        <div class="hero-card-val">Diploma Programme Available</div>
                    </div>
                </div>
                <div class="hero-card fade-in visible">
                    <div class="hero-card-icon"><i class="fa fa-pills"></i></div>
                    <div>
                        <div class="hero-card-name">Pharmacy</div>
                        <div class="hero-card-val">Certificate & Diploma Tracks</div>
                    </div>
                </div>
                <div class="hero-card fade-in visible">
                    <div class="hero-card-icon"><i class="fa fa-flask"></i></div>
                    <div>
                        <div class="hero-card-name">Medical Laboratory</div>
                        <div class="hero-card-val">Hands-on Lab Training</div>
                    </div>
                </div>
                <div class="hero-card fade-in visible">
                    <div class="hero-card-icon"><i class="fa fa-laptop-medical"></i></div>
                    <div>
                        <div class="hero-card-name">Online Diploma</div>
                        <div class="hero-card-val">Flexible Distance Learning</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- About --}}
<section class="section about-section" id="about">
    <div class="container">
        <div class="about-inner">
            <div>
                <span class="eyebrow">About KIHP</span>
                <h2 class="section-title">Shaping the Future of<br>Healthcare in Uganda</h2>
                <p style="font-size:.97rem;line-height:1.8;color:var(--body);margin-bottom:16px;">
                    <strong>{{ $school->name }}</strong> is a licensed and accredited health training
                    institution based in Kampala, Uganda. We provide education and training in health
                    and related fields for the community, Uganda, East Africa, and the rest of the world.
                </p>
                <p style="font-size:.95rem;line-height:1.8;color:var(--muted);margin-bottom:24px;">
                    Our programmes are designed to produce competent, compassionate, and professional
                    health workers — equipped with both the theoretical foundation and practical skills
                    needed to deliver quality healthcare services wherever they are deployed.
                </p>
                <div class="about-meta">
                    <div class="about-meta-row">
                        <i class="fa fa-location-dot"></i>
                        <span>{{ $school->address }}</span>
                    </div>
                    <div class="about-meta-row">
                        <i class="fa fa-phone"></i>
                        <span><a href="tel:{{ $school->phone_number }}">{{ $school->phone_number }}</a>
                        @if($school->phone_number_2) &nbsp;|&nbsp; <a href="tel:{{ $school->phone_number_2 }}">{{ $school->phone_number_2 }}</a>@endif</span>
                    </div>
                    <div class="about-meta-row">
                        <i class="fa fa-envelope"></i>
                        <span><a href="mailto:{{ $school->email }}">{{ $school->email }}</a></span>
                    </div>
                    @if($school->website)
                    <div class="about-meta-row">
                        <i class="fa fa-globe"></i>
                        <span><a href="{{ $school->website }}" target="_blank" rel="noopener">{{ $school->website }}</a></span>
                    </div>
                    @endif
                    @if($school->hm_name)
                    <div class="about-meta-row">
                        <i class="fa fa-user-tie"></i>
                        <span>Principal: <strong>{{ $school->hm_name }}</strong></span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="about-logo-wrap">
                <img src="{{ url('storage/' . $school->logo) }}" alt="{{ $school->name }} Logo" onerror="this.outerHTML='<div style=\'font-size:3rem;color:var(--cyan);font-weight:900;position:relative;z-index:1\'>KIHP</div>'">
                <div class="about-logo-motto">
                    <em>Our Motto</em>
                    "{{ $school->motto }}"
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Programmes --}}
<section class="section programs-section" id="programs">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">Academic Programmes</span>
            <h2 class="section-title">Courses We Offer</h2>
            <p class="section-lead">All programmes are structured to meet Uganda Allied Health Examinations Board (UAHEB) and Health Professions Council standards.</p>
        </div>

        <div class="programs-grid">
            @php
            $programMeta = [
                'DIPLOMA IN CLINICAL MEDICINE AND COMMUNITY HEALTH' => [
                    'type' => 'dip', 'icon' => 'fa fa-stethoscope', 'icon_bg' => '#e0f0ff', 'icon_color' => '#0b2040',
                    'desc' => 'A comprehensive clinical programme training you to diagnose, treat and manage patients across all health facility levels.',
                ],
                'DIPLOMA IN CLINICAL MEDICINE ONLINE' => [
                    'type' => 'online', 'icon' => 'fa fa-laptop-medical', 'icon_bg' => '#fff8e6', 'icon_color' => '#c8880e',
                    'desc' => 'Flexible distance-learning track for the Clinical Medicine Diploma — study from anywhere with internet access.',
                ],
                'DIPLOMA IN PHARMACY' => [
                    'type' => 'dip', 'icon' => 'fa fa-pills', 'icon_bg' => '#e0f0ff', 'icon_color' => '#0b2040',
                    'desc' => 'Trains pharmaceutical professionals in dispensing, compounding, patient counselling and medicines management.',
                ],
                'DIPLOMA IN MEDICAL LAB TECHNIQUES' => [
                    'type' => 'dip', 'icon' => 'fa fa-flask', 'icon_bg' => '#e0f0ff', 'icon_color' => '#0b2040',
                    'desc' => 'Covers haematology, microbiology, biochemistry and parasitology laboratory skills essential for clinical diagnostics.',
                ],
                'CERTIFICATE IN MEDICAL LABORATORY TECHNIQUES PROGRESSION RECORD' => [
                    'type' => 'cert', 'icon' => 'fa fa-vial', 'icon_bg' => '#e6f9ff', 'icon_color' => '#009fd9',
                    'desc' => 'Foundation certificate providing core laboratory techniques with pathways for progression to diploma level.',
                ],
                'CERTIFICATE IN MEDICAL RECORDS AND HEALTH INFORMATICS' => [
                    'type' => 'cert', 'icon' => 'fa fa-file-medical', 'icon_bg' => '#e6f9ff', 'icon_color' => '#009fd9',
                    'desc' => 'Covers health information management, patient records, coding, and digital health systems for health facilities.',
                ],
                'CERTIFICATE IN PHARMACY PROGRESSION RECORD' => [
                    'type' => 'cert', 'icon' => 'fa fa-capsules', 'icon_bg' => '#e6f9ff', 'icon_color' => '#009fd9',
                    'desc' => 'Entry-level pharmacy training with a clear progression pathway to the full Pharmacy Diploma.',
                ],
                'CERTIFICATE IN PUBLIC HEALTH' => [
                    'type' => 'cert', 'icon' => 'fa fa-heart-pulse', 'icon_bg' => '#e6f9ff', 'icon_color' => '#009fd9',
                    'desc' => 'Equips students with community health, disease surveillance, environmental health and health promotion competencies.',
                ],
            ];
            $uniquePrograms = $programs->unique()->sort()->values();
            @endphp

            @foreach($uniquePrograms as $prog)
            @php
                $meta = $programMeta[strtoupper(trim($prog))] ?? [
                    'type' => 'cert', 'icon' => 'fa fa-graduation-cap',
                    'icon_bg' => '#e6f9ff', 'icon_color' => '#009fd9',
                    'desc' => 'An accredited health professional training programme at KIHP.',
                ];
                $level = str_starts_with(strtoupper(trim($prog)), 'DIPLOMA') ? 'Diploma' : 'Certificate';
                $displayName = ucwords(strtolower(preg_replace('/ PROGRESSION RECORD$/', '', trim($prog))));
            @endphp
            <div class="program-card {{ $meta['type'] }} fade-in">
                <span class="program-badge {{ $meta['type'] }}">{{ $level }}</span>
                <div class="program-icon" style="background:{{ $meta['icon_bg'] }};color:{{ $meta['icon_color'] }}">
                    <i class="{{ $meta['icon'] }}"></i>
                </div>
                <h3>{{ $displayName }}</h3>
                <p>{{ $meta['desc'] }}</p>
                <a href="#admissions" class="program-arrow">
                    Learn more <i class="fa fa-arrow-right"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Why KIHP --}}
<section class="section features-section" id="why-kihp">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">Why Choose KIHP</span>
            <h2 class="section-title">Designed for Healthcare<br>Excellence</h2>
            <p class="section-lead">More than just a training institution — KIHP is committed to producing health professionals who make a lasting impact in their communities.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card fade-in">
                <div class="fi" style="background:#e6f9ff;color:var(--cyan)">
                    <i class="fa fa-certificate"></i>
                </div>
                <h3>Fully Accredited</h3>
                <p>All programmes are fully accredited and recognised by the Allied Health Professions Council of Uganda and relevant national examination bodies.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="fi" style="background:#e0f0ff;color:var(--navy-lt)">
                    <i class="fa fa-chalkboard-teacher"></i>
                </div>
                <h3>Qualified Lecturers</h3>
                <p>Our faculty comprises practising health professionals and experienced academic staff who bring real-world clinical expertise into the classroom.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="fi" style="background:#f0fdf4;color:#16a34a">
                    <i class="fa fa-hospital"></i>
                </div>
                <h3>Clinical Placements</h3>
                <p>Students complete structured clinical rotations at affiliated hospitals and health centres, building real-world competencies before graduation.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="fi" style="background:#fff8e6;color:var(--gold-dk)">
                    <i class="fa fa-laptop-medical"></i>
                </div>
                <h3>Online Learning</h3>
                <p>Our Diploma in Clinical Medicine is available online — designed for working professionals and students in remote areas without compromising quality.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="fi" style="background:#fdf4ff;color:#9333ea">
                    <i class="fa fa-graduation-cap"></i>
                </div>
                <h3>Clear Pathways</h3>
                <p>Certificate programmes include structured progression tracks to diploma level, helping students grow step by step toward full professional qualification.</p>
            </div>
            <div class="feature-card fade-in">
                <div class="fi" style="background:#fff1f2;color:#e11d48">
                    <i class="fa fa-users"></i>
                </div>
                <h3>Student Support</h3>
                <p>Dedicated academic advisors, online student portals, and a supportive learning environment ensure every student stays on track to succeed.</p>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="section stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-box fade-in">
                <div class="stat-box-icon"><i class="fa fa-users"></i></div>
                <div class="stat-box-val">{{ number_format($studentCount) }}<span>+</span></div>
                <div class="stat-box-lbl">Enrolled Students</div>
            </div>
            <div class="stat-box fade-in">
                <div class="stat-box-icon"><i class="fa fa-book-medical"></i></div>
                <div class="stat-box-val"><span>{{ $programs->count() }}</span></div>
                <div class="stat-box-lbl">Programmes</div>
            </div>
            <div class="stat-box fade-in">
                <div class="stat-box-icon"><i class="fa fa-laptop-medical"></i></div>
                <div class="stat-box-val"><span>1</span></div>
                <div class="stat-box-lbl">Online Programme</div>
            </div>
            <div class="stat-box fade-in">
                <div class="stat-box-icon"><i class="fa fa-map-location-dot"></i></div>
                <div class="stat-box-val"><span>KLA</span></div>
                <div class="stat-box-lbl">Kampala, Uganda</div>
            </div>
        </div>
    </div>
</section>

{{-- Admissions --}}
<section class="section admission-section" id="admissions">
    <div class="container">
        <div class="admission-inner">
            <div>
                <span class="eyebrow">Admissions</span>
                <h2 class="section-title">How to Apply</h2>
                <p class="section-lead" style="margin-bottom:0">We welcome applications from Uganda, East Africa, and beyond. Here's how the process works:</p>
                <div class="admission-steps">
                    <div class="admission-step fade-in">
                        <div class="step-num">1</div>
                        <div class="step-text">
                            <h4>Choose Your Programme</h4>
                            <p>Browse our certificate and diploma programmes and select the one that aligns with your career goals in healthcare.</p>
                        </div>
                    </div>
                    <div class="admission-step fade-in">
                        <div class="step-num">2</div>
                        <div class="step-text">
                            <h4>Submit Your Application</h4>
                            <p>Apply online or visit our Rubaga Road, Kampala campus. Bring your academic documents, national ID and passport photo.</p>
                        </div>
                    </div>
                    <div class="admission-step fade-in">
                        <div class="step-num">3</div>
                        <div class="step-text">
                            <h4>Selection & Admission Letter</h4>
                            <p>Successful applicants receive an admission letter and guidance on fees payment, orientation and registration procedures.</p>
                        </div>
                    </div>
                    <div class="admission-step fade-in">
                        <div class="step-num">4</div>
                        <div class="step-text">
                            <h4>Register & Begin Classes</h4>
                            <p>Complete registration, access your student portal and begin your journey toward a rewarding career in health.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admission-cta-box">
                <div style="font-size:3rem;margin-bottom:16px;color:var(--cyan);opacity:.6">
                    <i class="fa fa-user-graduate"></i>
                </div>
                <h3>Ready to Start?</h3>
                <p>
                    Begin your journey toward becoming a qualified health professional.
                    Applications are reviewed on a rolling basis.
                </p>
                <a href="{{ url('apply') }}" class="btn-apply-primary">
                    <i class="fa fa-user-plus"></i> Start Online Application
                </a>
                <a href="tel:{{ $school->phone_number }}" class="btn-apply-outline">
                    <i class="fa fa-phone"></i> Call Us to Apply
                </a>
                <p style="margin-top:20px;font-size:.78rem;color:rgba(255,255,255,.35);line-height:1.6;">
                    You can also visit us at<br>
                    <strong style="color:rgba(255,255,255,.55)">{{ $school->address }}</strong>
                </p>
            </div>
        </div>
    </div>
</section>

{{-- Contact --}}
<section class="section contact-section" id="contact">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">Get in Touch</span>
            <h2 class="section-title">Contact Us</h2>
            <p class="section-lead">We're here to answer your questions about admissions, programmes, and student life at KIHP.</p>
        </div>
        <div class="contact-inner">
            <div class="contact-cards">
                <div class="contact-card fade-in">
                    <div class="contact-icon"><i class="fa fa-location-dot"></i></div>
                    <div>
                        <h4>Physical Address</h4>
                        <p>{{ $school->address }}, Uganda</p>
                    </div>
                </div>
                <div class="contact-card fade-in">
                    <div class="contact-icon"><i class="fa fa-phone"></i></div>
                    <div>
                        <h4>Phone</h4>
                        <p>
                            <a href="tel:{{ $school->phone_number }}">{{ $school->phone_number }}</a>
                            @if($school->phone_number_2)
                            <br><a href="tel:{{ $school->phone_number_2 }}">{{ $school->phone_number_2 }}</a>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="contact-card fade-in">
                    <div class="contact-icon"><i class="fa fa-envelope"></i></div>
                    <div>
                        <h4>Email</h4>
                        <p><a href="mailto:{{ $school->email }}">{{ $school->email }}</a></p>
                    </div>
                </div>
                @if($school->website)
                <div class="contact-card fade-in">
                    <div class="contact-icon"><i class="fa fa-globe"></i></div>
                    <div>
                        <h4>Website</h4>
                        <p><a href="{{ $school->website }}" target="_blank" rel="noopener">{{ $school->website }}</a></p>
                    </div>
                </div>
                @endif
                <div class="contact-card fade-in">
                    <div class="contact-icon"><i class="fa fa-comment-dots"></i></div>
                    <div>
                        <h4>WhatsApp Enquiries</h4>
                        <p>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $school->phone_number) }}" target="_blank" rel="noopener">
                                Chat on WhatsApp <i class="fa fa-arrow-up-right-from-square" style="font-size:.75rem"></i>
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="contact-map-placeholder fade-in">
                <i class="fa fa-map-location-dot"></i>
                <h4>{{ $school->name }}</h4>
                <p>{{ $school->address }}, Kampala, Uganda</p>
                <a href="https://maps.google.com?q={{ urlencode($school->name . ', ' . $school->address . ', Kampala, Uganda') }}"
                   target="_blank" rel="noopener" class="contact-map-btn">
                    <i class="fa fa-location-arrow"></i> Open in Google Maps
                </a>
            </div>
        </div>
    </div>
</section>

{{-- CTA Strip --}}
<section class="cta-strip">
    <div class="container">
        <h2>Start Your Health Career Today</h2>
        <p>Join {{ number_format($studentCount) }}+ students already enrolled at KIHP — Uganda's trusted health training institute.</p>
        <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
            <a href="{{ url('apply') }}" class="btn-cta-cyan">
                <i class="fa fa-user-plus"></i> Apply Now
            </a>
            <a href="{{ url('admin/auth/login') }}" class="btn-cta">
                <i class="fa fa-sign-in-alt"></i> Student Portal
            </a>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">
                    <img src="{{ url('storage/' . $school->logo) }}" alt="{{ $school->short_name }}" onerror="this.style.display='none'">
                    <div class="footer-brand-t">
                        <strong>{{ $school->name }}</strong>
                        <span>{{ $school->address }}, Uganda</span>
                    </div>
                </div>
                <p class="footer-about">
                    An accredited health professional training institution offering certificate and
                    diploma programmes in Clinical Medicine, Pharmacy, Medical Laboratory,
                    Public Health and Health Informatics.
                </p>
                <div class="footer-motto">"{{ $school->motto }}"</div>
            </div>

            <div class="footer-col">
                <h4>Programmes</h4>
                <ul>
                    <li><span>Dip. Clinical Medicine</span></li>
                    <li><span>Dip. Clinical Medicine (Online)</span></li>
                    <li><span>Dip. Pharmacy</span></li>
                    <li><span>Dip. Medical Lab Techniques</span></li>
                    <li><span>Cert. Public Health</span></li>
                    <li><span>Cert. Medical Records</span></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="{{ url('admin/auth/login') }}">Student Portal Login</a></li>
                    <li><a href="{{ url('apply') }}">Apply Online</a></li>
                    <li><a href="#programs">View Programmes</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                    @if($school->website)
                    <li><a href="{{ $school->website }}" target="_blank" rel="noopener">Official Website</a></li>
                    @endif
                    <li>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $school->phone_number) }}" target="_blank" rel="noopener">
                            WhatsApp Us
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© {{ date('Y') }} {{ $school->name }}. All rights reserved.</span>
            <span class="footer-sys-badge">
                <i class="fa fa-circle" style="font-size:.4rem"></i>
                Powered by <a href="{{ url('/') }}" style="color:var(--cyan);margin-left:4px;">SchoolDynamics</a>
            </span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
(function () {
    'use strict';

    /* ── Navbar scroll class ─────────────────── */
    var nav = document.getElementById('mainNav');
    function onScroll() { nav.classList.toggle('scrolled', window.scrollY > 20); }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ── Mobile nav ──────────────────────────── */
    var mobileNav = document.getElementById('mobileNav');
    document.getElementById('hamburger').addEventListener('click', function () {
        mobileNav.classList.add('open');
        this.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    });
    document.getElementById('mobileClose').addEventListener('click', closeMobileNav);
    mobileNav.addEventListener('click', function (e) { if (e.target === mobileNav) closeMobileNav(); });
    function closeMobileNav() {
        mobileNav.classList.remove('open');
        document.getElementById('hamburger').setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }
    window.closeMobileNav = closeMobileNav;

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobileNav.classList.contains('open')) closeMobileNav();
    });

    /* ── Smooth anchor scroll ────────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                var top = target.getBoundingClientRect().top + window.scrollY - 76;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
        });
    });

    /* ── Fade-in on scroll ───────────────────── */
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.10 });
        document.querySelectorAll('.fade-in').forEach(function (el) { io.observe(el); });
    } else {
        document.querySelectorAll('.fade-in').forEach(function (el) { el.classList.add('visible'); });
    }
})();
</script>

</body>
</html>
