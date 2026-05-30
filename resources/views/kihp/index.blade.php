<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>KIHP — Kampala Institute of Health Professionals | Every Life Counts</title>
<meta name="description" content="KIHP — Accredited health professional training in Clinical Medicine, Pharmacy, Medical Laboratory, Public Health. Plot 201B Albert Cook Road, Kampala. Affordable fees. Online & weekend options.">
<meta name="keywords" content="KIHP, Kampala Institute Health Professionals, Clinical Medicine Uganda, Pharmacy training Kampala, Medical Laboratory, Public Health Uganda">
<link rel="canonical" href="{{ url('/kihp') }}">
<link rel="shortcut icon" href="{{ url('storage/' . $school->logo) }}" type="image/jpeg">
<meta property="og:title" content="KIHP — Every Life Counts | Health Training Kampala">
<meta property="og:description" content="Accredited certificate & diploma programmes. {{ $studentCount }}+ graduates. Kampala, Uganda.">
<meta property="og:image" content="{{ url('kihp/hero-bg.jpg') }}">
<meta property="og:url" content="{{ url('/kihp') }}">
<meta name="twitter:card" content="summary_large_image">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">

<style>
/* ── Reset & Variables ──────────────────────────────────── */
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
:root{
  --brand:#0EBEF2; --brand-dk:#0aa8d9; --brand-lt:#42cef5; --brand-tint:#e6f9fe;
  --navy:#061325; --navy-md:#0b2040; --navy-lt:#153259;
  --gold:#F4A71D; --gold-dk:#c8880e;
  --wa:#25D366; --wa-dk:#1da850;
  --text:#0f172a; --body:#374151; --muted:#6b7280;
  --border:#e5e7eb; --surface:#f0f9ff; --white:#fff;
  --r-sm:6px; --r:12px; --r-lg:20px; --r-xl:28px;
  --sh-sm:0 1px 4px rgba(0,0,0,.06);
  --sh:0 4px 20px rgba(0,0,0,.09);
  --sh-lg:0 12px 40px rgba(0,0,0,.14);
}
html{scroll-behavior:smooth;font-size:16px}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif;color:var(--body);background:var(--white);line-height:1.65;-webkit-font-smoothing:antialiased;overflow-x:hidden}
img{max-width:100%;height:auto;display:block}
a{color:inherit;text-decoration:none}

/* ── Reading Progress Bar ───────────────────────────────── */
#progress-bar{position:fixed;top:0;left:0;height:3px;width:0;background:linear-gradient(90deg,var(--brand),var(--gold));z-index:9999;transition:width .1s linear;border-radius:0 2px 2px 0}

/* ── Utilities ──────────────────────────────────────────── */
.container{max-width:1180px;margin:0 auto;padding:0 clamp(16px,5vw,48px)}
.section{padding:clamp(64px,8vw,100px) 0}
.eyebrow{display:inline-block;font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--brand);margin-bottom:10px}
.section-title{font-size:clamp(1.7rem,3.5vw,2.55rem);font-weight:800;color:var(--text);line-height:1.15;letter-spacing:-.03em;margin-bottom:14px}
.section-lead{font-size:1.02rem;color:var(--muted);max-width:560px;line-height:1.8}
.section-header{margin-bottom:52px}
.tag{display:inline-block;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;padding:3px 10px;border-radius:100px}

/* ── Announcement Bar ───────────────────────────────────── */
.announce{background:linear-gradient(90deg,var(--navy) 0%,var(--navy-md) 100%);color:var(--white);text-align:center;padding:10px 16px;font-size:.82rem;font-weight:500;position:relative;z-index:901}
.announce a{color:var(--brand);font-weight:700;text-decoration:underline}
.announce strong{color:var(--gold)}
.announce-close{position:absolute;right:16px;top:50%;transform:translateY(-50%);background:none;border:none;color:rgba(255,255,255,.5);cursor:pointer;font-size:1rem;padding:4px}
.announce-close:hover{color:#fff}

/* ── Navbar ─────────────────────────────────────────────── */
.nav{position:sticky;top:0;z-index:900;height:66px;display:flex;align-items:center;background:rgba(255,255,255,.92);backdrop-filter:blur(16px) saturate(180%);-webkit-backdrop-filter:blur(16px) saturate(180%);border-bottom:1px solid transparent;transition:border-color .3s,box-shadow .3s}
.nav.scrolled{border-color:var(--border);box-shadow:0 2px 20px rgba(14,190,242,.1)}
.nav-inner{display:flex;align-items:center;justify-content:space-between;gap:20px;width:100%}
.nav-brand{display:flex;align-items:center;gap:11px;flex-shrink:0}
.nav-brand img{height:36px;width:auto;object-fit:contain;border-radius:5px}
.nav-brand-name{font-size:.95rem;font-weight:900;color:var(--navy);line-height:1.2;letter-spacing:-.02em}
.nav-brand-sub{font-size:.6rem;font-weight:500;color:var(--muted);letter-spacing:.06em;text-transform:uppercase}
.nav-links{display:flex;align-items:center;gap:2px;list-style:none}
.nav-links a{font-size:.83rem;font-weight:500;color:var(--body);padding:8px 11px;border-radius:var(--r-sm);transition:color .2s,background .2s}
.nav-links a:hover{color:var(--brand);background:var(--brand-tint)}
.btn-nav-wa{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:var(--wa);color:#fff;font-size:.83rem;font-weight:700;border-radius:var(--r-sm);transition:background .2s,transform .2s;box-shadow:0 2px 10px rgba(37,211,102,.3);white-space:nowrap}
.btn-nav-wa:hover{background:var(--wa-dk);transform:translateY(-1px)}
.nav-hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:6px;background:none;border:none}
.nav-hamburger span{display:block;width:22px;height:2px;background:var(--text);border-radius:2px;transition:all .3s}

/* ── Mobile Nav ─────────────────────────────────────────── */
.mobile-nav{display:none;position:fixed;inset:0;z-index:800;background:rgba(6,19,37,.97);flex-direction:column;align-items:center;justify-content:center;gap:8px}
.mobile-nav.open{display:flex}
.mobile-nav a{font-size:1.15rem;font-weight:600;color:#fff;padding:13px 32px;border-radius:var(--r-sm);transition:background .2s;width:260px;text-align:center}
.mobile-nav a:hover{background:rgba(255,255,255,.08)}
.mobile-nav-close{position:absolute;top:20px;right:24px;background:none;border:none;color:rgba(255,255,255,.5);font-size:1.5rem;cursor:pointer}
.mobile-nav-wa{background:var(--wa)!important;color:#fff!important;font-weight:800!important;margin-top:16px}

/* ── Floating WA Button ─────────────────────────────────── */
.wa-fab{position:fixed;bottom:90px;right:24px;z-index:800;width:60px;height:60px;border-radius:50%;background:var(--wa);display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#fff;box-shadow:0 6px 24px rgba(37,211,102,.45);transition:transform .2s,box-shadow .2s;text-decoration:none}
.wa-fab:hover{transform:scale(1.1);box-shadow:0 8px 32px rgba(37,211,102,.6)}
.wa-fab::before,.wa-fab::after{content:'';position:absolute;width:100%;height:100%;border-radius:50%;border:2px solid var(--wa);animation:waRing 2.5s ease-out infinite}
.wa-fab::after{animation-delay:.8s}
@keyframes waRing{0%{transform:scale(1);opacity:.7}100%{transform:scale(2.2);opacity:0}}
.wa-fab-tooltip{position:absolute;right:72px;background:var(--navy);color:#fff;font-size:.75rem;font-weight:600;padding:6px 12px;border-radius:var(--r-sm);white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .2s;box-shadow:var(--sh)}
.wa-fab-tooltip::after{content:'';position:absolute;right:-6px;top:50%;transform:translateY(-50%);border:6px solid transparent;border-left-color:var(--navy);border-right:none}
.wa-fab:hover .wa-fab-tooltip{opacity:1}

/* ── Mobile Sticky Bar ──────────────────────────────────── */
.mobile-cta-bar{display:none;position:fixed;bottom:0;left:0;right:0;z-index:799;padding:10px 12px;background:rgba(6,19,37,.97);backdrop-filter:blur(10px);gap:10px;border-top:1px solid rgba(255,255,255,.08)}
.mobile-cta-bar .btn-wa-mob{flex:1;display:flex;align-items:center;justify-content:center;gap:8px;padding:13px 10px;background:var(--wa);color:#fff;font-size:.9rem;font-weight:800;border-radius:var(--r-sm);transition:background .2s}
.mobile-cta-bar .btn-call-mob{display:flex;align-items:center;justify-content:center;gap:8px;padding:13px 16px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:.9rem;font-weight:700;border-radius:var(--r-sm);white-space:nowrap;transition:background .2s}

/* ── Hero ───────────────────────────────────────────────── */
.hero{position:relative;min-height:100svh;display:flex;align-items:center;overflow:hidden;padding-top:0}
.hero-bg{position:absolute;inset:0;background-image:url('{{ url("kihp/hero-bg.jpg") }}');background-size:cover;background-position:center 30%;z-index:0;animation:kenBurns 22s ease-in-out infinite alternate}
@keyframes kenBurns{0%{background-size:105%;background-position:center 30%}100%{background-size:115%;background-position:center 40%}}
.hero-bg::after{content:'';position:absolute;inset:0;background:linear-gradient(110deg,rgba(6,19,37,.92) 0%,rgba(11,32,64,.82) 50%,rgba(14,190,242,.25) 100%)}
.hero-bg::before{content:'';position:absolute;inset:0;z-index:1;background-image:radial-gradient(circle,rgba(255,255,255,.08) 1px,transparent 1px);background-size:28px 28px}
.hero-inner{position:relative;z-index:2;padding:clamp(80px,12vw,140px) 0 clamp(80px,10vw,120px);max-width:720px}
.hero-badge{display:inline-flex;align-items:center;gap:9px;padding:7px 16px;background:rgba(14,190,242,.15);border:1px solid rgba(14,190,242,.4);border-radius:100px;font-size:.7rem;font-weight:700;color:var(--brand);letter-spacing:.09em;text-transform:uppercase;margin-bottom:24px}
.hero-badge-dot{width:7px;height:7px;border-radius:50%;background:var(--brand);animation:blink 2s ease infinite}
@keyframes blink{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(1.4)}}
.hero-title{font-size:clamp(2.4rem,6vw,4rem);font-weight:900;color:#fff;line-height:1.07;letter-spacing:-.04em;margin-bottom:6px}
.hero-title .serif{font-family:'Playfair Display',serif;font-style:italic;color:var(--brand)}
.hero-sub{font-size:clamp(1.1rem,2vw,1.4rem);font-weight:700;color:var(--gold);letter-spacing:.01em;margin-bottom:20px}
.hero-desc{font-size:1.02rem;color:rgba(255,255,255,.78);line-height:1.8;max-width:540px;margin-bottom:36px}
.hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:44px}
.btn-wa-hero{display:inline-flex;align-items:center;gap:10px;padding:16px 32px;background:var(--wa);color:#fff;font-size:1rem;font-weight:800;border-radius:var(--r-sm);box-shadow:0 8px 28px rgba(37,211,102,.4);transition:transform .2s,box-shadow .2s;white-space:nowrap}
.btn-wa-hero:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(37,211,102,.5)}
.btn-call-hero{display:inline-flex;align-items:center;gap:10px;padding:15px 24px;border:2px solid rgba(255,255,255,.35);color:#fff;font-size:.93rem;font-weight:700;border-radius:var(--r-sm);transition:background .2s,border-color .2s}
.btn-call-hero:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.75)}
.hero-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border:1px solid rgba(255,255,255,.14);border-radius:var(--r);overflow:hidden;background:rgba(255,255,255,.05);backdrop-filter:blur(10px)}
.h-stat{padding:18px 14px;text-align:center;border-right:1px solid rgba(255,255,255,.1)}
.h-stat:last-child{border-right:none}
.h-stat-val{font-size:1.7rem;font-weight:900;color:#fff;line-height:1}
.h-stat-val span{color:var(--brand)}
.h-stat-lbl{font-size:.62rem;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.08em;margin-top:5px}
.hero-scroll{position:absolute;bottom:32px;left:50%;transform:translateX(-50%);z-index:2;display:flex;flex-direction:column;align-items:center;gap:6px;color:rgba(255,255,255,.45);font-size:.72rem;letter-spacing:.1em;text-transform:uppercase;cursor:pointer}
.hero-scroll-line{width:1px;height:36px;background:linear-gradient(to bottom,rgba(255,255,255,.4),transparent);animation:scrollPulse 2s ease infinite}
@keyframes scrollPulse{0%,100%{opacity:.4;transform:scaleY(1)}50%{opacity:1;transform:scaleY(1.2)}}

/* ── About ──────────────────────────────────────────────── */
.about-section{background:var(--surface)}
.about-inner{display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center}
.about-text p{font-size:.97rem;line-height:1.85;color:var(--body);margin-bottom:14px}
.about-text p strong{color:var(--navy)}
.about-vm{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:28px 0}
.about-vm-card{padding:20px;background:var(--white);border:1px solid var(--border);border-radius:var(--r);border-top:3px solid var(--brand)}
.about-vm-card h4{font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--brand);margin-bottom:8px}
.about-vm-card p{font-size:.82rem;color:var(--muted);line-height:1.7}
.about-meta{display:flex;flex-direction:column;gap:9px;margin-top:20px}
.about-meta-row{display:flex;align-items:center;gap:10px;font-size:.84rem;color:var(--muted)}
.about-meta-row i{color:var(--brand);width:16px;flex-shrink:0}
.about-meta-row a{color:var(--brand)}
.about-visual{position:relative}
.about-img-main{width:100%;height:420px;object-fit:cover;object-position:center top;border-radius:var(--r-lg);box-shadow:var(--sh-lg)}
.about-img-inset{position:absolute;bottom:-20px;left:-24px;width:180px;height:140px;object-fit:cover;object-position:center top;border-radius:var(--r);border:4px solid #fff;box-shadow:var(--sh-lg);z-index:2}
.about-badge{position:absolute;top:20px;right:20px;background:var(--navy);color:#fff;padding:10px 16px;border-radius:var(--r-sm);font-size:.75rem;font-weight:700;text-align:center;z-index:2;box-shadow:var(--sh)}
.about-badge strong{display:block;font-size:1.5rem;font-weight:900;color:var(--brand);line-height:1}
.sister-banner{margin-top:18px;padding:12px 16px;background:var(--white);border:1px solid var(--border);border-radius:var(--r-sm);font-size:.8rem;color:var(--muted);display:flex;align-items:center;gap:10px}
.sister-banner i{color:var(--brand);flex-shrink:0}

/* ── Values ─────────────────────────────────────────────── */
.values-section{background:linear-gradient(135deg,var(--navy) 0%,var(--navy-md) 100%);position:relative;overflow:hidden}
.values-section::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(14,190,242,.09) 1px,transparent 1px);background-size:26px 26px}
.values-section .eyebrow{color:var(--gold)}
.values-section .section-title{color:#fff}
.values-grid{position:relative;z-index:1;display:grid;grid-template-columns:repeat(5,1fr);gap:14px}
.value-card{padding:28px 20px;text-align:center;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:var(--r);transition:background .25s,transform .25s,border-color .25s}
.value-card:hover{background:rgba(14,190,242,.12);border-color:rgba(14,190,242,.35);transform:translateY(-5px)}
.value-icon{width:56px;height:56px;border-radius:14px;background:rgba(14,190,242,.15);border:1px solid rgba(14,190,242,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.4rem;color:var(--brand)}
.value-card h3{font-size:.9rem;font-weight:700;color:#fff;margin-bottom:8px}
.value-card p{font-size:.78rem;color:rgba(255,255,255,.55);line-height:1.65}

/* ── Programmes ─────────────────────────────────────────── */
.programs-section{background:var(--white)}
.programs-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
.prog-card{border-radius:var(--r);overflow:hidden;border:1.5px solid var(--border);background:var(--white);transition:border-color .25s,box-shadow .25s,transform .3s;display:flex;flex-direction:column}
.prog-card:hover{border-color:var(--brand);box-shadow:0 10px 40px rgba(14,190,242,.15);transform:translateY(-6px)}
.prog-card-img{height:160px;overflow:hidden;position:relative;flex-shrink:0}
.prog-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease}
.prog-card:hover .prog-card-img img{transform:scale(1.08)}
.prog-card-img-grad{position:absolute;inset:0;background:linear-gradient(to top,rgba(6,19,37,.5) 0%,transparent 60%)}
.prog-card-img-fallback{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2.5rem}
.prog-card-body{padding:18px 16px 20px;flex:1;display:flex;flex-direction:column}
.prog-level{font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;padding:3px 9px;border-radius:100px;margin-bottom:10px;display:inline-block}
.prog-level.cert{background:var(--brand-tint);color:var(--brand-dk)}
.prog-level.dip{background:#dce8ff;color:#1e40af}
.prog-level.online{background:#fef9e6;color:var(--gold-dk)}
.prog-card h3{font-size:.9rem;font-weight:700;color:var(--text);line-height:1.35;margin-bottom:8px;flex:1}
.prog-card p{font-size:.78rem;color:var(--muted);line-height:1.65;margin-bottom:14px}
.prog-card-cta{display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:700;color:var(--brand);transition:gap .2s}
.prog-card-cta i{font-size:.7rem;transition:transform .2s}
.prog-card:hover .prog-card-cta i{transform:translateX(4px)}

/* ── Why KIHP ───────────────────────────────────────────── */
.why-section{background:var(--surface)}
.why-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.why-card{padding:28px 24px 30px;border:1px solid var(--border);border-radius:var(--r);background:var(--white);transition:border-color .25s,box-shadow .25s,transform .25s}
.why-card:hover{border-color:var(--brand);box-shadow:0 8px 32px rgba(14,190,242,.12);transform:translateY(-4px)}
.why-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;margin-bottom:18px}
.why-card h3{font-size:.97rem;font-weight:700;color:var(--text);margin-bottom:8px}
.why-card p{font-size:.86rem;color:var(--muted);line-height:1.75}

/* ── Gallery ────────────────────────────────────────────── */
.gallery-section{background:var(--navy)}
.gallery-section .eyebrow{color:var(--gold)}
.gallery-section .section-title{color:#fff}
.gallery-section .section-lead{color:rgba(255,255,255,.55)}
.gallery-grid{display:grid;grid-template-columns:repeat(4,1fr);grid-auto-rows:220px;gap:8px}
.gal-item{position:relative;overflow:hidden;border-radius:var(--r-sm);cursor:pointer}
.gal-item:nth-child(1),.gal-item:nth-child(5),.gal-item:nth-child(8){grid-row:span 2}
.gal-item img{width:100%;height:100%;object-fit:cover;transition:transform .55s cubic-bezier(.25,.46,.45,.94)}
.gal-item::after{content:'';position:absolute;inset:0;background:linear-gradient(to top,rgba(6,19,37,.6) 0%,transparent 60%);opacity:0;transition:opacity .3s}
.gal-item:hover img{transform:scale(1.07)}
.gal-item:hover::after{opacity:1}
.gal-overlay{position:absolute;inset:0;z-index:2;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .3s}
.gal-item:hover .gal-overlay{opacity:1}
.gal-zoom{width:46px;height:46px;border-radius:50%;background:rgba(255,255,255,.2);border:2px solid rgba(255,255,255,.6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem;backdrop-filter:blur(4px);transform:scale(.8);transition:transform .3s}
.gal-item:hover .gal-zoom{transform:scale(1)}

/* ── Testimonials ───────────────────────────────────────── */
.testi-section{background:var(--white)}
.testi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px}
.testi-card{padding:28px 24px;border:1px solid var(--border);border-radius:var(--r);background:var(--surface);position:relative;transition:box-shadow .25s,transform .25s}
.testi-card:hover{box-shadow:var(--sh-lg);transform:translateY(-4px)}
.testi-stars{display:flex;gap:3px;color:var(--gold);font-size:.85rem;margin-bottom:14px}
.testi-text{font-size:.9rem;color:var(--body);line-height:1.8;margin-bottom:20px;font-style:italic}
.testi-text::before{content:'\201C';font-size:2.5rem;color:var(--brand);opacity:.3;line-height:0;vertical-align:-18px;margin-right:3px;font-family:Georgia,serif}
.testi-author{display:flex;align-items:center;gap:12px}
.testi-avatar{width:46px;height:46px;border-radius:50%;object-fit:cover;object-position:top;border:2px solid var(--brand)}
.testi-name{font-size:.85rem;font-weight:700;color:var(--text)}
.testi-prog{font-size:.74rem;color:var(--muted)}
.testi-quote-mark{position:absolute;top:20px;right:22px;font-size:4rem;color:var(--brand);opacity:.07;font-family:Georgia,serif;line-height:1}

/* ── Admissions ─────────────────────────────────────────── */
.admit-section{background:var(--surface)}
.admit-inner{display:grid;grid-template-columns:1.1fr 1fr;gap:64px;align-items:center}
.admit-steps{display:flex;flex-direction:column;gap:14px;margin-top:28px}
.admit-step{display:flex;align-items:flex-start;gap:16px;padding:18px 20px;background:var(--white);border:1px solid var(--border);border-radius:var(--r);transition:border-color .2s,box-shadow .2s}
.admit-step:hover{border-color:var(--brand);box-shadow:var(--sh-sm)}
.step-num{width:38px;height:38px;flex-shrink:0;border-radius:50%;background:var(--brand);color:#fff;display:flex;align-items:center;justify-content:center;font-size:.9rem;font-weight:900;box-shadow:0 3px 10px rgba(14,190,242,.3)}
.step-body h4{font-size:.9rem;font-weight:700;color:var(--text);margin-bottom:3px}
.step-body p{font-size:.81rem;color:var(--muted);line-height:1.65}
.admit-poster{border-radius:var(--r-xl);overflow:hidden;position:relative;box-shadow:var(--sh-lg)}
.admit-poster img{width:100%;display:block;border-radius:var(--r-xl)}
.admit-poster-cta{position:absolute;bottom:0;left:0;right:0;padding:20px;background:linear-gradient(to top,rgba(6,19,37,.95) 60%,transparent);border-radius:0 0 var(--r-xl) var(--r-xl);display:flex;flex-direction:column;gap:10px}
.btn-wa-admit{display:flex;align-items:center;justify-content:center;gap:10px;padding:15px 20px;background:var(--wa);color:#fff;font-size:.95rem;font-weight:800;border-radius:var(--r-sm);transition:background .2s,transform .2s;box-shadow:0 4px 16px rgba(37,211,102,.35)}
.btn-wa-admit:hover{background:var(--wa-dk);transform:translateY(-2px)}
.btn-call-admit{display:flex;align-items:center;justify-content:center;gap:8px;padding:12px;border:1.5px solid rgba(255,255,255,.25);color:#fff;font-size:.85rem;font-weight:700;border-radius:var(--r-sm);transition:background .2s,border-color .2s}
.btn-call-admit:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.6)}

/* ── CTA Strip ──────────────────────────────────────────── */
.cta-strip{background:linear-gradient(135deg,var(--navy) 0%,var(--navy-md) 100%);padding:clamp(56px,7vw,88px) 0;text-align:center;position:relative;overflow:hidden}
.cta-strip::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle,rgba(14,190,242,.08) 1px,transparent 1px);background-size:24px 24px}
.cta-strip>*{position:relative;z-index:1}
.cta-strip h2{font-size:clamp(1.8rem,4vw,2.8rem);font-weight:900;color:#fff;letter-spacing:-.04em;margin-bottom:12px}
.cta-strip h2 span{color:var(--brand)}
.cta-strip p{font-size:1.02rem;color:rgba(255,255,255,.6);max-width:480px;margin:0 auto 36px;line-height:1.75}
.cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.btn-cta-wa{display:inline-flex;align-items:center;gap:10px;padding:17px 36px;background:var(--wa);color:#fff;font-size:1.02rem;font-weight:800;border-radius:var(--r-sm);box-shadow:0 8px 28px rgba(37,211,102,.35);transition:transform .2s,box-shadow .2s}
.btn-cta-wa:hover{transform:translateY(-3px);box-shadow:0 12px 36px rgba(37,211,102,.5)}
.btn-cta-call{display:inline-flex;align-items:center;gap:10px;padding:16px 28px;border:2px solid rgba(255,255,255,.25);color:#fff;font-size:.97rem;font-weight:700;border-radius:var(--r-sm);transition:background .2s,border-color .2s}
.btn-cta-call:hover{background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.7)}

/* ── Footer ─────────────────────────────────────────────── */
.footer{background:#040d1a;color:rgba(255,255,255,.45);padding:60px 0 0}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:52px;padding-bottom:48px}
.footer-logo-row{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.footer-logo-row img{height:40px;border-radius:6px;background:#fff;padding:3px}
.footer-logo-row strong{display:block;font-size:.9rem;font-weight:800;color:#fff}
.footer-logo-row span{font-size:.7rem;color:rgba(255,255,255,.35)}
.footer-about{font-size:.82rem;line-height:1.8;max-width:360px;margin-bottom:16px}
.footer-motto{font-size:.8rem;font-weight:700;color:var(--brand);padding:9px 14px;background:rgba(14,190,242,.08);border:1px solid rgba(14,190,242,.2);border-radius:var(--r-sm);display:inline-block}
.footer-col h4{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.12em;margin-bottom:18px}
.footer-col ul{list-style:none;display:flex;flex-direction:column;gap:10px}
.footer-col ul li{font-size:.82rem}
.footer-col ul li a{color:rgba(255,255,255,.45);transition:color .2s}
.footer-col ul li a:hover{color:var(--brand)}
.footer-col ul li span{color:rgba(255,255,255,.3)}
.footer-bottom{border-top:1px solid rgba(255,255,255,.06);padding:20px 0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;font-size:.76rem}
.footer-bottom a{color:rgba(255,255,255,.25);transition:color .2s}
.footer-bottom a:hover{color:rgba(255,255,255,.7)}
.footer-sys{display:inline-flex;align-items:center;gap:6px;font-size:.7rem;padding:4px 10px;background:rgba(14,190,242,.08);border:1px solid rgba(14,190,242,.15);border-radius:100px;color:var(--brand)}

/* ── Scroll Animations ──────────────────────────────────── */
.fade-up{opacity:0;transform:translateY(28px);transition:opacity .6s ease,transform .6s ease}
.fade-up.visible{opacity:1;transform:none}
.fade-in{opacity:0;transition:opacity .6s ease}
.fade-in.visible{opacity:1}
.slide-left{opacity:0;transform:translateX(-28px);transition:opacity .65s ease,transform .65s ease}
.slide-left.visible{opacity:1;transform:none}
.slide-right{opacity:0;transform:translateX(28px);transition:opacity .65s ease,transform .65s ease}
.slide-right.visible{opacity:1;transform:none}
.stagger-1{transition-delay:.1s}
.stagger-2{transition-delay:.2s}
.stagger-3{transition-delay:.3s}
.stagger-4{transition-delay:.4s}
.stagger-5{transition-delay:.5s}

/* ── Responsive ─────────────────────────────────────────── */
@media(max-width:1100px){
  .programs-grid{grid-template-columns:repeat(3,1fr)}
  .values-grid{grid-template-columns:repeat(3,1fr)}
  .gallery-grid{grid-template-columns:repeat(3,1fr)}
  .gal-item:nth-child(1),.gal-item:nth-child(5),.gal-item:nth-child(8){grid-row:auto}
}
@media(max-width:900px){
  .about-inner,.admit-inner{grid-template-columns:1fr;gap:40px}
  .about-vm{grid-template-columns:1fr}
  .why-grid{grid-template-columns:repeat(2,1fr)}
  .testi-grid{grid-template-columns:1fr;max-width:480px;margin:0 auto}
  .footer-grid{grid-template-columns:1fr 1fr;gap:32px}
  .hero-stats{grid-template-columns:repeat(2,1fr)}
  .h-stat:nth-child(2){border-right:none}
  .h-stat:nth-child(3),.h-stat:nth-child(4){border-top:1px solid rgba(255,255,255,.1)}
  .admit-poster{max-width:480px;margin:0 auto}
}
@media(max-width:768px){
  .nav-links{display:none}
  .nav-hamburger{display:flex}
  .programs-grid{grid-template-columns:repeat(2,1fr)}
  .gallery-grid{grid-template-columns:repeat(2,1fr);grid-auto-rows:180px}
  .values-grid{grid-template-columns:repeat(2,1fr)}
  .why-grid{grid-template-columns:1fr}
  .footer-grid{grid-template-columns:1fr;gap:28px}
  .hero-stats{display:none}
  .mobile-cta-bar{display:flex}
  .wa-fab{bottom:80px}
  body{padding-bottom:72px}
}
@media(max-width:540px){
  .programs-grid{grid-template-columns:1fr}
  .gallery-grid{grid-template-columns:repeat(2,1fr);grid-auto-rows:150px}
  .values-grid{grid-template-columns:1fr 1fr}
  .hero-actions .btn-call-hero{display:none}
  .footer-bottom{flex-direction:column;text-align:center}
}
</style>
</head>
<body>

{{-- Reading Progress --}}
<div id="progress-bar"></div>

{{-- Announcement Bar --}}
<div class="announce" id="announceBar">
    <strong>🎓 Admissions Open — 2026/2027</strong> &nbsp;|&nbsp;
    Weekend & Online options available &nbsp;|&nbsp;
    <a href="#" onclick="openWA(event)">Chat with Admissions on WhatsApp →</a>
    <button class="announce-close" onclick="document.getElementById('announceBar').remove()">✕</button>
</div>

{{-- Floating WhatsApp Button --}}
<a href="#" onclick="openWA(event)" class="wa-fab" aria-label="Chat on WhatsApp">
    <i class="fa-brands fa-whatsapp"></i>
    <span class="wa-fab-tooltip">Chat with Admissions</span>
</a>

{{-- Mobile Sticky Bar --}}
<div class="mobile-cta-bar">
    <a href="#" onclick="openWA(event)" class="btn-wa-mob"><i class="fa-brands fa-whatsapp"></i> Apply on WhatsApp</a>
    <a href="tel:+256774750076" class="btn-call-mob"><i class="fa fa-phone"></i> Call</a>
</div>

{{-- Mobile Nav --}}
<div class="mobile-nav" id="mobileNav">
    <button class="mobile-nav-close" id="mobileClose"><i class="fa fa-times"></i></button>
    <a href="#about" onclick="closeMobileNav()">About</a>
    <a href="#programmes" onclick="closeMobileNav()">Programmes</a>
    <a href="#gallery" onclick="closeMobileNav()">Gallery</a>
    <a href="#admissions" onclick="closeMobileNav()">Admissions</a>
    <a href="#contact" onclick="closeMobileNav()">Contact</a>
    <a href="#" onclick="openWA(event);closeMobileNav()" class="mobile-nav-wa"><i class="fa-brands fa-whatsapp"></i> &nbsp;Apply on WhatsApp</a>
</div>

{{-- Navbar --}}
<nav class="nav" id="mainNav">
    <div class="container">
        <div class="nav-inner">
            <a href="{{ url('/kihp') }}" class="nav-brand">
                <img src="{{ url('storage/' . $school->logo) }}" alt="KIHP" onerror="this.style.display='none'">
                <div>
                    <div class="nav-brand-name">KIHP</div>
                    <div class="nav-brand-sub">Kampala, Uganda</div>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="#about">About</a></li>
                <li><a href="#programmes">Programmes</a></li>
                <li><a href="#gallery">Gallery</a></li>
                <li><a href="#admissions">Admissions</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <a href="#" onclick="openWA(event)" class="btn-nav-wa"><i class="fa-brands fa-whatsapp"></i> Apply Now</a>
            <button class="nav-hamburger" id="hamburger" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
</nav>

{{-- ═══════════════════════════════════════════════════════ HERO --}}
<section class="hero" id="top">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-inner">
            <div class="hero-badge">
                <span class="hero-badge-dot"></span>
                Plot 201B Albert Cook Road · Kampala, Uganda
            </div>
            <h1 class="hero-title">
                Train with<br>
                <span class="serif">Purpose.</span><br>
                Heal with Impact.
            </h1>
            <p class="hero-sub">"Every Life Counts" — {{ $school->name }}</p>
            <p class="hero-desc">
                Uganda's trusted institute for health professional training. Accredited certificate
                and diploma programmes in Clinical Medicine, Pharmacy, Medical Laboratory,
                Public Health, and more — at affordable fees with online options available.
            </p>
            <div class="hero-actions">
                <a href="#" onclick="openWA(event)" class="btn-wa-hero">
                    <i class="fa-brands fa-whatsapp"></i> Apply on WhatsApp
                </a>
                <a href="tel:+256774750076" class="btn-call-hero">
                    <i class="fa fa-phone"></i> Call to Enquire
                </a>
            </div>
            <div class="hero-stats">
                <div class="h-stat">
                    <div class="h-stat-val"><span class="count-up" data-target="{{ $studentCount }}">0</span><span>+</span></div>
                    <div class="h-stat-lbl">Students</div>
                </div>
                <div class="h-stat">
                    <div class="h-stat-val"><span>{{ $programs->count() }}</span></div>
                    <div class="h-stat-lbl">Programmes</div>
                </div>
                <div class="h-stat">
                    <div class="h-stat-val"><i class="fa fa-wifi" style="font-size:1.4rem;color:var(--brand)"></i></div>
                    <div class="h-stat-lbl">Online Option</div>
                </div>
                <div class="h-stat">
                    <div class="h-stat-val"><i class="fa fa-calendar-days" style="font-size:1.4rem;color:var(--gold)"></i></div>
                    <div class="h-stat-lbl">Weekends Too</div>
                </div>
            </div>
        </div>
    </div>
    <a href="#about" class="hero-scroll">
        <span>Scroll</span>
        <div class="hero-scroll-line"></div>
    </a>
</section>

{{-- ═══════════════════════════════════════════════════════ ABOUT --}}
<section class="section about-section" id="about">
    <div class="container">
        <div class="about-inner">
            <div class="about-text slide-left">
                <span class="eyebrow">About KIHP</span>
                <h2 class="section-title">Who We Are</h2>
                <p>
                    <strong>Kampala Institute of Health Professionals (KIHP)</strong> is a licensed and accredited medical
                    training institution located at <strong>Plot 201B Albert Cook Road, Kampala</strong>. We are a sister
                    school of <em>Vine Paramedical School</em> in Kadugala, Masaka, and the <em>Vine Medicare Clinic Chain</em> —
                    a network that has proven excellence in the medical field over many years.
                </p>
                <p>
                    We provide education and training in health and related fields for the community of Uganda,
                    East Africa, and the world — equipping graduates with the clinical knowledge, practical skills,
                    and professional values needed to deliver quality healthcare wherever they serve.
                </p>
                <div class="about-vm">
                    <div class="about-vm-card fade-up stagger-1">
                        <h4><i class="fa fa-eye"></i> &nbsp;Our Vision</h4>
                        <p>Dedicated to excellence in health care, research, quality patient care and service — to improve the health of all citizens of Uganda.</p>
                    </div>
                    <div class="about-vm-card fade-up stagger-2">
                        <h4><i class="fa fa-bullseye"></i> &nbsp;Our Mission</h4>
                        <p>A comprehensive model of health care services, education and economic empowerment to help communities alleviate poverty and disease.</p>
                    </div>
                </div>
                <div class="about-meta">
                    <div class="about-meta-row"><i class="fa fa-location-dot"></i><span>Plot 201B Albert Cook Road, Kampala, Uganda</span></div>
                    <div class="about-meta-row"><i class="fa-brands fa-whatsapp"></i><span><a href="#" onclick="openWA(event)">+256 708 343 674</a> &nbsp;(WhatsApp / Admissions)</span></div>
                    <div class="about-meta-row"><i class="fa fa-phone"></i><span><a href="tel:+256774750076">+256 774 750 076</a></span></div>
                    <div class="about-meta-row"><i class="fa fa-globe"></i><span><a href="https://kihp.ac.ug" target="_blank" rel="noopener">kihp.ac.ug</a></span></div>
                </div>
                <div class="sister-banner fade-up stagger-3">
                    <i class="fa fa-link"></i>
                    <span>Sister institution: <strong>Vine Paramedical School, Kadugala, Masaka</strong> &amp; Vine Medicare Clinic Chain</span>
                </div>
            </div>

            <div class="about-visual slide-right">
                <img src="{{ url('kihp/faculty-group.jpg') }}" alt="KIHP Faculty in academic regalia" class="about-img-main">
                <img src="{{ url('kihp/principal.jpg') }}" alt="KIHP Principal addressing graduation" class="about-img-inset">
                <div class="about-badge">
                    <strong class="count-up" data-target="{{ $studentCount }}">0</strong>
                    <span style="font-size:.7rem;color:rgba(255,255,255,.6);display:block;margin-top:2px">Graduates</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ VALUES --}}
<section class="section values-section">
    <div class="container">
        <div class="section-header" style="text-align:center">
            <span class="eyebrow">Our Core Values</span>
            <h2 class="section-title" style="margin-bottom:6px">What We Stand For</h2>
            <p class="section-lead" style="max-width:480px;margin:0 auto;color:rgba(255,255,255,.5)">Every decision at KIHP is guided by these values — in the classroom, the clinic, and the community.</p>
        </div>
        <div class="values-grid">
            <div class="value-card fade-up stagger-1">
                <div class="value-icon"><i class="fa fa-heart"></i></div>
                <h3>Compassion</h3>
                <p>We train health workers who treat every patient with empathy, understanding, and genuine care.</p>
            </div>
            <div class="value-card fade-up stagger-2">
                <div class="value-icon"><i class="fa fa-person-rays"></i></div>
                <h3>Dignity</h3>
                <p>We uphold the dignity of every student, patient, and community member in all our practices.</p>
            </div>
            <div class="value-card fade-up stagger-3">
                <div class="value-icon"><i class="fa fa-lock"></i></div>
                <h3>Confidentiality</h3>
                <p>Patient privacy and data protection are core disciplines taught and practiced from day one.</p>
            </div>
            <div class="value-card fade-up stagger-4">
                <div class="value-icon"><i class="fa fa-star"></i></div>
                <h3>Professionalism</h3>
                <p>High standards of conduct, accountability, and excellence in every aspect of our work.</p>
            </div>
            <div class="value-card fade-up stagger-5">
                <div class="value-icon"><i class="fa fa-scale-balanced"></i></div>
                <h3>Transparency</h3>
                <p>Open, honest communication with students, parents, and the broader health community.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ PROGRAMMES --}}
<section class="section programs-section" id="programmes">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">Academic Programmes</span>
            <h2 class="section-title">Courses We Offer</h2>
            <p class="section-lead">All programmes are accredited by the Allied Health Professions Council of Uganda. Flexible study modes — weekday, weekend, and online.</p>
        </div>
        @php
        $progMeta = [
            'DIPLOMA IN CLINICAL MEDICINE AND COMMUNITY HEALTH' => [
                'type'=>'dip','img'=>'clinical.jpg','icon'=>'fa-stethoscope','bg'=>'#dce8ff','fg'=>'#1e40af',
                'desc'=>'Trains clinical officers to diagnose, treat and manage patients across all healthcare facility levels. Includes community health rotations.',
            ],
            'DIPLOMA IN CLINICAL MEDICINE ONLINE' => [
                'type'=>'online','img'=>'clinical.jpg','icon'=>'fa-laptop-medical','bg'=>'#fef9e6','fg'=>'#b45309',
                'desc'=>'Flexible online track for the Diploma in Clinical Medicine — designed for working professionals and students in remote areas.',
            ],
            'DIPLOMA IN PHARMACY' => [
                'type'=>'dip','img'=>'pharmacy-lab.jpg','icon'=>'fa-pills','bg'=>'#dce8ff','fg'=>'#1e40af',
                'desc'=>'Covers pharmaceutical sciences, dispensing, patient counselling, and medicines management for pharmacy professionals.',
            ],
            'DIPLOMA IN MEDICAL LAB TECHNIQUES' => [
                'type'=>'dip','img'=>'med-lab.jpg','icon'=>'fa-flask','bg'=>'#dce8ff','fg'=>'#1e40af',
                'desc'=>'Laboratory sciences including haematology, microbiology, biochemistry and parasitology for clinical diagnostics.',
            ],
            'CERTIFICATE IN MEDICAL LABORATORY TECHNIQUES PROGRESSION RECORD' => [
                'type'=>'cert','img'=>'med-lab.jpg','icon'=>'fa-vial','bg'=>null,'fg'=>null,
                'desc'=>'Foundation certificate in laboratory techniques with a clear progression pathway toward the full diploma qualification.',
            ],
            'CERTIFICATE IN MEDICAL RECORDS AND HEALTH INFORMATICS' => [
                'type'=>'cert','img'=>null,'icon'=>'fa-file-medical','bg'=>null,'fg'=>null,
                'desc'=>'Health information management, patient records, clinical coding, and digital health systems for health facilities.',
            ],
            'CERTIFICATE IN PHARMACY PROGRESSION RECORD' => [
                'type'=>'cert','img'=>'pharmacy-shelves.jpg','icon'=>'fa-capsules','bg'=>null,'fg'=>null,
                'desc'=>'Entry-level pharmacy training with a structured progression pathway to the Diploma in Pharmacy.',
            ],
            'CERTIFICATE IN PUBLIC HEALTH' => [
                'type'=>'cert','img'=>'clinical.jpg','icon'=>'fa-heart-pulse','bg'=>null,'fg'=>null,
                'desc'=>'Community health, disease surveillance, environmental health and health promotion competencies.',
            ],
        ];
        $imgGrads = ['#0EBEF2','#0aa8d9','#153259','#0b2040'];
        @endphp

        <div class="programs-grid">
            @foreach($programs->unique()->sort()->values() as $i => $prog)
            @php
                $key = strtoupper(trim($prog));
                $meta = $progMeta[$key] ?? ['type'=>'cert','img'=>null,'icon'=>'fa-graduation-cap','bg'=>null,'fg'=>null,'desc'=>'Accredited health professional training at KIHP.'];
                $level = str_starts_with($key,'DIPLOMA') ? 'Diploma' : 'Certificate';
                $displayName = ucwords(strtolower(preg_replace('/ PROGRESSION RECORD$/i','',trim($prog))));
                $isOnline = str_contains(strtolower($prog),'online');
            @endphp
            <div class="prog-card fade-up stagger-{{ ($i % 4) + 1 }}">
                <div class="prog-card-img">
                    @if($meta['img'])
                        <img src="{{ url('kihp/' . $meta['img']) }}" alt="{{ $displayName }}" loading="lazy">
                    @else
                        <div class="prog-card-img-fallback" style="background:linear-gradient(135deg,{{ $imgGrads[$i % 4] }},var(--navy-md))">
                            <i class="fa {{ $meta['icon'] }}" style="color:rgba(255,255,255,.4)"></i>
                        </div>
                    @endif
                    <div class="prog-card-img-grad"></div>
                </div>
                <div class="prog-card-body">
                    <span class="prog-level {{ $meta['type'] }}">{{ $level }}{{ $isOnline ? ' · Online' : '' }}</span>
                    <h3>{{ $displayName }}</h3>
                    <p>{{ $meta['desc'] }}</p>
                    <a href="#" onclick="openWA(event)" class="prog-card-cta">
                        Enquire Now <i class="fa fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ WHY KIHP --}}
<section class="section why-section" id="why">
    <div class="container">
        <div class="section-header" style="text-align:center">
            <span class="eyebrow">Why Choose KIHP</span>
            <h2 class="section-title">Your Success Is Our Mission</h2>
            <p class="section-lead" style="margin:0 auto">Join thousands of graduates who have shaped their careers in healthcare through KIHP.</p>
        </div>
        <div class="why-grid">
            <div class="why-card fade-up stagger-1">
                <div class="why-icon" style="background:#e6f9fe;color:var(--brand)"><i class="fa fa-certificate"></i></div>
                <h3>Fully Accredited</h3>
                <p>All programmes are accredited by the Allied Health Professions Council (AHPC) and recognised by Uganda's examination bodies — your qualification carries real weight.</p>
            </div>
            <div class="why-card fade-up stagger-2">
                <div class="why-icon" style="background:#e0f0ff;color:#1e40af"><i class="fa fa-hospital"></i></div>
                <h3>Clinical Placements</h3>
                <p>Structured clinical rotations at affiliated hospitals and health centres give you real hands-on experience before you graduate — not just theory.</p>
            </div>
            <div class="why-card fade-up stagger-3">
                <div class="why-icon" style="background:#f0fdf4;color:#15803d"><i class="fa fa-wallet"></i></div>
                <h3>Affordable Fees</h3>
                <p>Quality health training at fees that are accessible for Ugandan families. Flexible payment plans available — education shouldn't be out of reach.</p>
            </div>
            <div class="why-card fade-up stagger-4">
                <div class="why-icon" style="background:#fef9e6;color:var(--gold-dk)"><i class="fa fa-laptop-medical"></i></div>
                <h3>Online & Weekend Options</h3>
                <p>Study on your schedule. Our Diploma in Clinical Medicine is available fully online, and diploma programmes offer weekend classes for working professionals.</p>
            </div>
            <div class="why-card fade-up stagger-5">
                <div class="why-icon" style="background:#fdf4ff;color:#7e22ce"><i class="fa fa-arrows-up-to-line"></i></div>
                <h3>Clear Career Pathways</h3>
                <p>Certificate programmes include structured progression to diploma level — grow step by step toward full professional qualification and better career prospects.</p>
            </div>
            <div class="why-card fade-up stagger-1">
                <div class="why-icon" style="background:#fff1f2;color:#be123c"><i class="fa fa-users"></i></div>
                <h3>Community Focus</h3>
                <p>We are committed to training health workers who serve their communities — including outreach in immunisation, nutrition, reproductive health, and HIV/AIDS care.</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ GALLERY --}}
<section class="section gallery-section" id="gallery">
    <div class="container">
        <div class="section-header">
            <span class="eyebrow">Life at KIHP</span>
            <h2 class="section-title">Graduation & Achievements</h2>
            <p class="section-lead">Real moments from our graduation ceremonies — students and faculty celebrating years of hard work and dedication to healthcare.</p>
        </div>

        @php
        $galleryImages = [
            ['src'=>'gallery/gal-01.jpg','caption'=>'KIHP Principal presenting graduation certificates'],
            ['src'=>'gallery/gal-02.jpg','caption'=>'Faculty speech during graduation ceremony'],
            ['src'=>'gallery/gal-03.jpg','caption'=>'KIHP graduates marching in procession'],
            ['src'=>'gallery/gal-04.jpg','caption'=>'Graduates taking the healthcare oath'],
            ['src'=>'gallery/gal-05.jpg','caption'=>'Celebrating graduates in KIHP regalia'],
            ['src'=>'gallery/gal-06.jpg','caption'=>'Proud graduates at the ceremony'],
            ['src'=>'gallery/gal-07.jpg','caption'=>'Faculty processing in academic regalia'],
            ['src'=>'gallery/gal-08.jpg','caption'=>'A joyful moment at KIHP graduation'],
            ['src'=>'gallery/gal-09.jpg','caption'=>'Graduands with their achievement certificates'],
        ];
        @endphp

        <div class="gallery-grid">
            @foreach($galleryImages as $img)
            <a href="{{ url('kihp/' . $img['src']) }}" class="gal-item glightbox fade-in" data-gallery="kihp-gallery" data-description="{{ $img['caption'] }}">
                <img src="{{ url('kihp/' . $img['src']) }}" alt="{{ $img['caption'] }}" loading="lazy">
                <div class="gal-overlay"><div class="gal-zoom"><i class="fa fa-expand"></i></div></div>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ TESTIMONIALS --}}
<section class="section testi-section">
    <div class="container">
        <div class="section-header" style="text-align:center">
            <span class="eyebrow">Student Voices</span>
            <h2 class="section-title">What Our Students Say</h2>
            <p class="section-lead" style="margin:0 auto">Hear from students who chose KIHP to build their healthcare careers.</p>
        </div>
        <div class="testi-grid" style="max-width:900px;margin:0 auto">
            <div class="testi-card fade-up stagger-1">
                <div class="testi-stars"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                <p class="testi-text">The clinical placements at KIHP gave me real hands-on experience. By the time I graduated, I felt truly ready to work in a health facility. The instructors are professionals who genuinely care about our success.</p>
                <div class="testi-author">
                    <img src="{{ url('kihp/student-1.jpg') }}" alt="KIHP Student" class="testi-avatar">
                    <div>
                        <div class="testi-name">KIHP Graduate</div>
                        <div class="testi-prog">Diploma in Clinical Medicine</div>
                    </div>
                </div>
                <div class="testi-quote-mark">"</div>
            </div>
            <div class="testi-card fade-up stagger-2">
                <div class="testi-stars"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i></div>
                <p class="testi-text">I chose KIHP because of the affordable fees and the weekday flexibility. The online diploma option was a game changer for me — I could study while still supporting my family. Highly recommended!</p>
                <div class="testi-author">
                    <img src="{{ url('kihp/student-2.jpg') }}" alt="KIHP Student" class="testi-avatar">
                    <div>
                        <div class="testi-name">KIHP Graduate</div>
                        <div class="testi-prog">Diploma in Pharmacy</div>
                    </div>
                </div>
                <div class="testi-quote-mark">"</div>
            </div>
            <div class="testi-card fade-up stagger-3">
                <div class="testi-stars"><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star-half-stroke"></i></div>
                <p class="testi-text">KIHP prepared me for the real world of healthcare. The lecturers have practical experience, not just theory. Our graduation ceremony was a proud moment — and it showed me KIHP truly celebrates its students.</p>
                <div class="testi-author">
                    <img src="{{ url('kihp/student-3.jpg') }}" alt="KIHP Student" class="testi-avatar">
                    <div>
                        <div class="testi-name">KIHP Graduate</div>
                        <div class="testi-prog">Certificate in Medical Laboratory</div>
                    </div>
                </div>
                <div class="testi-quote-mark">"</div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ ADMISSIONS --}}
<section class="section admit-section" id="admissions">
    <div class="container">
        <div class="admit-inner">
            <div class="slide-left">
                <span class="eyebrow">Admissions</span>
                <h2 class="section-title">How to Join KIHP</h2>
                <p class="section-lead" style="margin-bottom:0">Getting started is simple — just follow these steps and reach out to our team.</p>
                <div class="admit-steps">
                    <div class="admit-step fade-up stagger-1">
                        <div class="step-num">1</div>
                        <div class="step-body">
                            <h4>Choose Your Programme</h4>
                            <p>Browse our 8 accredited health programmes. Certificate and diploma levels available, with online and weekend options.</p>
                        </div>
                    </div>
                    <div class="admit-step fade-up stagger-2">
                        <div class="step-num">2</div>
                        <div class="step-body">
                            <h4>Contact Our Admissions Team</h4>
                            <p>WhatsApp or call us on <strong>+256 708 343 674</strong> or visit us at Plot 201B Albert Cook Road, Kampala. We'll guide you through requirements.</p>
                        </div>
                    </div>
                    <div class="admit-step fade-up stagger-3">
                        <div class="step-num">3</div>
                        <div class="step-body">
                            <h4>Submit Documents & Register</h4>
                            <p>Bring your academic certificates, national ID and passport photos. Pay affordable admission fees and receive your student number.</p>
                        </div>
                    </div>
                    <div class="admit-step fade-up stagger-4">
                        <div class="step-num">4</div>
                        <div class="step-body">
                            <h4>Begin Your Health Career</h4>
                            <p>Attend orientation, access the student portal and join the KIHP family — Uganda's next generation of health professionals.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admit-poster slide-right">
                <img src="{{ url('kihp/admission-poster.jpg') }}" alt="KIHP Admissions 2026">
                <div class="admit-poster-cta">
                    <a href="#" onclick="openWA(event)" class="btn-wa-admit">
                        <i class="fa-brands fa-whatsapp"></i> Start Application on WhatsApp
                    </a>
                    <a href="tel:+256774750076" class="btn-call-admit">
                        <i class="fa fa-phone"></i> Call: +256 774 750 076
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ CONTACT --}}
<section class="section" id="contact" style="background:var(--white)">
    <div class="container">
        <div class="section-header" style="text-align:center">
            <span class="eyebrow">Find Us</span>
            <h2 class="section-title">Get in Touch</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:18px;max-width:900px;margin:0 auto">
            <div style="padding:24px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);display:flex;align-items:flex-start;gap:14px" class="fade-up stagger-1">
                <div style="width:42px;height:42px;border-radius:10px;background:var(--brand-tint);color:var(--brand);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa fa-location-dot"></i></div>
                <div><h4 style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:4px">Address</h4><p style="font-size:.9rem;color:var(--text);line-height:1.6">Plot 201B Albert Cook Road,<br>Kampala, Uganda</p></div>
            </div>
            <div style="padding:24px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);display:flex;align-items:flex-start;gap:14px" class="fade-up stagger-2">
                <div style="width:42px;height:42px;border-radius:10px;background:#e6ffe6;color:var(--wa);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa-brands fa-whatsapp"></i></div>
                <div><h4 style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:4px">WhatsApp</h4><p style="font-size:.9rem;line-height:1.6"><a href="#" onclick="openWA(event)" style="color:var(--wa);font-weight:700">+256 708 343 674</a><br><span style="font-size:.78rem;color:var(--muted)">Admissions team</span></p></div>
            </div>
            <div style="padding:24px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);display:flex;align-items:flex-start;gap:14px" class="fade-up stagger-3">
                <div style="width:42px;height:42px;border-radius:10px;background:var(--brand-tint);color:var(--brand);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa fa-phone"></i></div>
                <div><h4 style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:4px">Phone</h4><p style="font-size:.9rem;line-height:1.6"><a href="tel:+256774750076" style="color:var(--brand);font-weight:700">+256 774 750 076</a></p></div>
            </div>
            <div style="padding:24px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);display:flex;align-items:flex-start;gap:14px" class="fade-up stagger-4">
                <div style="width:42px;height:42px;border-radius:10px;background:var(--brand-tint);color:var(--brand);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa fa-globe"></i></div>
                <div><h4 style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:4px">Website</h4><p style="font-size:.9rem;line-height:1.6"><a href="https://kihp.ac.ug" target="_blank" rel="noopener" style="color:var(--brand)">kihp.ac.ug</a></p></div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ FINAL CTA --}}
<section class="cta-strip">
    <div class="container">
        <h2>Ready to <span>Change Lives?</span><br>Start with Yours.</h2>
        <p>Join {{ number_format($studentCount) }}+ students who have built their healthcare careers at KIHP — Uganda's trusted health training institute.</p>
        <div class="cta-btns">
            <a href="#" onclick="openWA(event)" class="btn-cta-wa">
                <i class="fa-brands fa-whatsapp"></i> Apply Now on WhatsApp
            </a>
            <a href="tel:+256774750076" class="btn-cta-call">
                <i class="fa fa-phone"></i> Call +256 774 750 076
            </a>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════ FOOTER --}}
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <div class="footer-logo-row">
                    <img src="{{ url('storage/' . $school->logo) }}" alt="KIHP" onerror="this.style.display='none'">
                    <div><strong>{{ $school->short_name }}</strong><span>Kampala Institute of Health Professionals</span></div>
                </div>
                <p class="footer-about">An accredited health professional training institution offering certificate and diploma programmes in Clinical Medicine, Pharmacy, Medical Laboratory and Public Health — serving Uganda, East Africa and beyond.</p>
                <div class="footer-motto">"Every Life Counts"</div>
            </div>
            <div class="footer-col">
                <h4>Programmes</h4>
                <ul>
                    <li><span>Dip. Clinical Medicine</span></li>
                    <li><span>Dip. Clinical Medicine Online</span></li>
                    <li><span>Dip. Pharmacy</span></li>
                    <li><span>Dip. Medical Lab</span></li>
                    <li><span>Cert. Public Health</span></li>
                    <li><span>Cert. Medical Records</span></li>
                    <li><span>Cert. Pharmacy</span></li>
                    <li><span>Cert. Med. Lab</span></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Contact</h4>
                <ul>
                    <li><span>Plot 201B Albert Cook Rd</span></li>
                    <li><span>Kampala, Uganda</span></li>
                    <li><a href="#" onclick="openWA(event)"><i class="fa-brands fa-whatsapp"></i> +256 708 343 674</a></li>
                    <li><a href="tel:+256774750076"><i class="fa fa-phone"></i> +256 774 750 076</a></li>
                    <li><a href="https://kihp.ac.ug" target="_blank" rel="noopener">kihp.ac.ug</a></li>
                    <li><a href="{{ url('admin/auth/login') }}">Student Portal</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© {{ date('Y') }} Kampala Institute of Health Professionals. All rights reserved.</span>
            <span class="footer-sys"><i class="fa fa-circle" style="font-size:.35rem"></i> Powered by <a href="{{ url('/') }}" style="color:var(--brand);margin-left:4px">SchoolDynamics</a></span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
(function(){
'use strict';

/* WhatsApp launcher */
var WA_NUM  = '256708343674';
var WA_MSG  = encodeURIComponent('Hello KIHP! I\'m interested in your health training programmes. Please send me more information about admissions and available courses. 🏥');
window.openWA = function(e){ if(e) e.preventDefault(); window.open('https://wa.me/'+WA_NUM+'?text='+WA_MSG,'_blank','noopener'); };

/* Reading progress bar */
var pb = document.getElementById('progress-bar');
window.addEventListener('scroll',function(){
    var h = document.documentElement;
    var pct = (h.scrollTop||document.body.scrollTop) / (h.scrollHeight - h.clientHeight) * 100;
    pb.style.width = Math.min(100,pct)+'%';
},{passive:true});

/* Navbar scroll */
var nav = document.getElementById('mainNav');
window.addEventListener('scroll',function(){ nav.classList.toggle('scrolled',window.scrollY>40); },{passive:true});

/* Mobile nav */
var mobileNav = document.getElementById('mobileNav');
document.getElementById('hamburger').addEventListener('click',function(){
    mobileNav.classList.add('open');
    this.setAttribute('aria-expanded','true');
    document.body.style.overflow='hidden';
});
document.getElementById('mobileClose').addEventListener('click',closeMobileNav);
mobileNav.addEventListener('click',function(e){ if(e.target===mobileNav) closeMobileNav(); });
function closeMobileNav(){
    mobileNav.classList.remove('open');
    document.getElementById('hamburger').setAttribute('aria-expanded','false');
    document.body.style.overflow='';
}
window.closeMobileNav=closeMobileNav;
document.addEventListener('keydown',function(e){ if(e.key==='Escape'&&mobileNav.classList.contains('open')) closeMobileNav(); });

/* Smooth scroll for anchor links */
document.querySelectorAll('a[href^="#"]').forEach(function(a){
    a.addEventListener('click',function(e){
        var target=document.querySelector(this.getAttribute('href'));
        if(target){ e.preventDefault(); window.scrollTo({top:target.getBoundingClientRect().top+window.scrollY-70,behavior:'smooth'}); }
    });
});

/* GLightbox */
GLightbox({ selector:'.glightbox', touchNavigation:true, loop:true, openEffect:'fade', closeEffect:'fade', slideEffect:'slide' });

/* Intersection Observer — scroll animations */
var io = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
        if(entry.isIntersecting){ entry.target.classList.add('visible'); io.unobserve(entry.target); }
    });
},{threshold:0.1});
document.querySelectorAll('.fade-up,.fade-in,.slide-left,.slide-right').forEach(function(el){ io.observe(el); });

/* Count-up animation */
function countUp(el){
    var target = parseInt(el.getAttribute('data-target'),10);
    var dur = 2000, interval = 16;
    var steps = dur / interval;
    var inc = target / steps;
    var current = 0;
    var timer = setInterval(function(){
        current += inc;
        if(current >= target){ current = target; clearInterval(timer); }
        el.textContent = Math.floor(current).toLocaleString();
    },interval);
}
var countObserver = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
        if(entry.isIntersecting){ countUp(entry.target); countObserver.unobserve(entry.target); }
    });
},{threshold:0.5});
document.querySelectorAll('.count-up').forEach(function(el){ countObserver.observe(el); });

/* Announcement bar close */
var ab = document.getElementById('announceBar');
if(ab){ setTimeout(function(){ if(ab.parentNode) ab.remove(); },12000); }

})();
</script>
</body>
</html>
