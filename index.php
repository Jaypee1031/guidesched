<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'student') {
        redirect('student/dashboard.php');
    } elseif ($role === 'counselor' || $role === 'admin') {
        redirect('admin/dashboard.php');
    }
}

$page_title = 'GuideSched — Guidance Counseling System — Cagasat High School';
$base_url_path = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <style>
      :root {
        --hero-bg: linear-gradient(135deg, #1E1B4B 0%, #3B0764 45%, #6D28D9 100%);
      }

      body {
        background-color: #F8FAFC;
      }

      .landing-nav {
        position: sticky;
        top: 0;
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 36px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--line);
        box-shadow: 0 2px 10px rgba(0,0,0,0.03);
      }

      .landing-hero {
        position: relative;
        background: var(--hero-bg);
        color: #FFFFFF;
        padding: 90px 24px 100px;
        text-align: center;
        overflow: hidden;
        border-radius: 0 0 32px 32px;
        box-shadow: 0 12px 40px -10px rgba(43, 17, 83, 0.35);
      }

      .hero-glow-1 {
        position: absolute;
        top: -60px;
        left: 50%;
        transform: translateX(-50%);
        width: 600px;
        height: 300px;
        background: radial-gradient(circle, rgba(168, 85, 247, 0.3) 0%, rgba(109, 40, 217, 0) 70%);
        pointer-events: none;
      }

      .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #F3E8FF;
        font-size: 13px;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 30px;
        margin-bottom: 24px;
        backdrop-filter: blur(8px);
        letter-spacing: 0.3px;
      }

      .landing-hero h1 {
        font-family: 'Sora', sans-serif;
        font-size: 46px;
        font-weight: 800;
        color: #FFFFFF !important;
        margin-bottom: 18px;
        line-height: 1.2;
        letter-spacing: -0.5px;
      }

      .hero-highlight {
        background: linear-gradient(135deg, #FDE047 0%, #FACC15 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
      }

      .landing-hero p.hero-desc {
        font-size: 17px;
        color: #E2E8F0;
        max-width: 660px;
        margin: 0 auto 36px;
        line-height: 1.6;
        font-weight: 400;
      }

      .hero-actions {
        display: flex;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
      }

      .btn-hero-primary {
        background: #FFFFFF !important;
        color: #5B21B6 !important;
        font-weight: 700 !important;
        font-size: 15px !important;
        padding: 14px 32px !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px -5px rgba(255, 255, 255, 0.35);
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
      }

      .btn-hero-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px -5px rgba(255, 255, 255, 0.5);
        background: #F8FAFC !important;
      }

      .btn-hero-secondary {
        background: rgba(255, 255, 255, 0.12) !important;
        color: #FFFFFF !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        padding: 14px 28px !important;
        border-radius: 12px !important;
        backdrop-filter: blur(8px);
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
      }

      .btn-hero-secondary:hover {
        background: rgba(255, 255, 255, 0.22) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        transform: translateY(-2px);
      }

      .trust-strip {
        max-width: 900px;
        margin: -35px auto 40px;
        position: relative;
        z-index: 10;
        background: #FFFFFF;
        border-radius: 16px;
        border: 1px solid #E2E8F0;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08);
        padding: 18px 28px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
      }

      .trust-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13.5px;
        font-weight: 700;
        color: #334155;
      }

      .trust-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #F1F5F9;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6D28D9;
      }

      .landing-section {
        max-width: 1120px;
        margin: 50px auto;
        padding: 0 24px;
      }

      .section-title h2 {
        font-size: 30px;
        font-weight: 800;
        color: #1E1B4B;
      }

      .feat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-top: 36px;
      }

      .feat-card {
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid #E2E8F0;
        padding: 30px 24px;
        box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
      }

      .feat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px -8px rgba(109, 40, 217, 0.15);
        border-color: #C084FC;
      }

      .feat-icon-bubble {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
      }

      .portal-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 36px;
      }

      .portal-card {
        background: #FFFFFF;
        border-radius: 20px;
        border: 1px solid #E2E8F0;
        padding: 32px;
        box-shadow: 0 4px 20px -5px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
      }

      .portal-card.student {
        border-top: 4px solid #6D28D9;
      }

      .portal-card.counselor {
        border-top: 4px solid #059669;
      }

      @media(max-width: 868px) {
        .feat-grid, .portal-grid { grid-template-columns: 1fr; }
        .landing-hero h1 { font-size: 32px; }
        .trust-strip { flex-direction: column; align-items: flex-start; margin-top: 20px; }
        .landing-nav { padding: 12px 18px; }
      }
    </style>
</head>
<body>

<?php include 'includes/icons.php'; ?>

<!-- NAVBAR -->
<nav class="landing-nav">
  <div class="brand">
    <div class="brand-mark">GS</div>
    <div class="brand-text">
      <div class="name">GuideSched</div>
      <div class="portal">CAGASAT HIGH SCHOOL</div>
    </div>
  </div>
  <div style="display:flex; gap:10px; align-items:center;">
    <a href="download-app.php" class="btn btn-outline btn-sm" title="Download GuideSched Mobile App">
      <span class="icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></span>Download App
    </a>
    <a href="login.php" class="btn btn-ghost btn-sm">Log In</a>
    <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
  </div>
</nav>

<!-- HERO BANNER -->
<div class="landing-hero">
  <div class="hero-glow-1"></div>
  <div class="hero-badge">
    <span>✨</span> Official Guidance Portal · Cagasat High School
  </div>
  <h1>Your Guidance. Your Schedule.<br><span class="hero-highlight">Your Well-being.</span></h1>
  <p class="hero-desc">Making guidance counseling accessible, confidential, and flexible for Cagasat High School students & guidance office staff.</p>
  <div class="hero-actions">
    <a href="register.php" class="btn-hero-primary">
      <span>Book an Appointment</span>
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
    <button onclick="triggerPWAInstall()" class="btn-hero-secondary">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      <span>Install App</span>
    </button>
  </div>
</div>

<!-- TRUST STRIP -->
<div class="trust-strip">
  <div class="trust-item">
    <div class="trust-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <span>100% Confidential & Secure</span>
  </div>
  <div class="trust-item">
    <div class="trust-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <span>Instant Slot Confirmation</span>
  </div>
  <div class="trust-item">
    <div class="trust-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
    </div>
    <span>Mobile PWA & App Support</span>
  </div>
</div>

<!-- FEATURES SECTION -->
<div class="landing-section">
  <div class="section-title" style="text-align:center;">
    <h2>Why Choose GuideSched?</h2>
    <p style="color:var(--muted); margin-top:8px; font-size:15px;">Simplified guidance scheduling built specifically for modern student care.</p>
  </div>

  <div class="feat-grid">
    <div class="feat-card">
      <div class="feat-icon-bubble" style="background:#EDE6FB; color:#6D28D9;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <h3 style="font-size:19px; color:#1E1B4B; margin-bottom:8px;">Easy Scheduling</h3>
      <p style="color:#64748B; font-size:14px; line-height:1.6;">Choose your preferred guidance counselor, date, and available weekday time slot with real-time confirmation.</p>
    </div>

    <div class="feat-card">
      <div class="feat-icon-bubble" style="background:#FEF3C7; color:#D97706;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      </div>
      <h3 style="font-size:19px; color:#1E1B4B; margin-bottom:8px;">Smart Notifications</h3>
      <p style="color:#64748B; font-size:14px; line-height:1.6;">Receive instant appointment reminders, approval notices, and status updates directly on your dashboard.</p>
    </div>

    <div class="feat-card">
      <div class="feat-icon-bubble" style="background:#D1FAE5; color:#059669;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <h3 style="font-size:19px; color:#1E1B4B; margin-bottom:8px;">Strict Confidentiality</h3>
      <p style="color:#64748B; font-size:14px; line-height:1.6;">Your appointments, concern categories, and counseling records are kept completely private and secure.</p>
    </div>
  </div>
</div>

<!-- PORTAL ROLES SECTION -->
<div class="landing-section" style="margin-top:70px;">
  <div class="section-title" style="text-align:center;">
    <h2>Tailored Guidance Experience</h2>
    <p style="color:var(--muted); margin-top:8px; font-size:15px;">Built to serve both students and guidance counselors seamlessly.</p>
  </div>

  <div class="portal-grid">
    <div class="portal-card student">
      <div>
        <div style="font-size:12px; font-weight:800; color:#6D28D9; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Student Care</div>
        <h3 style="font-size:22px; color:#1E1B4B; margin-bottom:10px;">Student Portal</h3>
        <p style="color:#64748B; font-size:14.5px; line-height:1.6; margin-bottom:24px;">Book guidance sessions, select concern categories, track upcoming appointments, and receive status updates on your mobile or desktop device.</p>
      </div>
      <div style="display:flex; gap:10px;">
        <a href="register.php" class="btn btn-primary btn-sm" style="padding:10px 20px;">Student Sign Up</a>
        <a href="login.php" class="btn btn-outline btn-sm" style="padding:10px 20px;">Student Login</a>
      </div>
    </div>

    <div class="portal-card counselor">
      <div>
        <div style="font-size:12px; font-weight:800; color:#059669; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px;">Guidance Staff</div>
        <h3 style="font-size:22px; color:#1E1B4B; margin-bottom:10px;">Counselor Portal</h3>
        <p style="color:#64748B; font-size:14.5px; line-height:1.6; margin-bottom:24px;">Review student requests, approve or reschedule sessions, manage weekday time slots, and view comprehensive guidance analytics.</p>
      </div>
      <div>
        <a href="login.php" class="btn btn-sm" style="background:#059669; color:#fff; padding:10px 20px; text-decoration:none; border-radius:10px; font-weight:700;">Counselor Login →</a>
      </div>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer style="text-align:center; padding: 32px 24px; border-top: 1px solid var(--line); color: var(--muted); font-size: 13.5px; background: #FFFFFF; margin-top:80px;">
  <div style="margin-bottom:10px; font-weight:700; color:var(--violet-950);">GuideSched — Guidance Office, Cagasat High School</div>
  &copy; <?php echo date('Y'); ?> All rights reserved. · <a href="download-app.php" style="color:var(--violet-600); text-decoration:none; font-weight:600;">Download App</a> · <a href="login.php" style="color:var(--violet-600); text-decoration:none; font-weight:600;">Sign In</a>
</footer>

</body>
</html>
