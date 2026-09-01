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
      .landing-hero {
        background: linear-gradient(135deg, var(--violet-950) 0%, var(--violet-800) 100%);
        color: var(--white);
        padding: 80px 24px;
        text-align: center;
        border-radius: 0 0 24px 24px;
      }
      .landing-hero h1 {
        font-family: 'Sora', sans-serif;
        font-size: 38px;
        font-weight: 800;
        margin-bottom: 16px;
        line-height: 1.2;
      }
      .landing-hero p {
        font-size: 16px;
        color: var(--violet-100);
        max-width: 600px;
        margin: 0 auto 28px;
        line-height: 1.6;
      }
      .feat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 32px;
      }
      .feat-card {
        background: var(--white);
        border-radius: var(--radius);
        border: 1px solid var(--line);
        padding: 24px;
        box-shadow: var(--shadow);
      }
      .feat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--violet-100);
        color: var(--violet-600);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
      }
      .landing-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 32px;
        background: var(--white);
        border-bottom: 1px solid var(--line);
      }
      .landing-section {
        max-width: 1100px;
        margin: 48px auto;
        padding: 0 24px;
      }
      @media(max-width: 768px) {
        .feat-grid { grid-template-columns: 1fr; }
        .landing-hero h1 { font-size: 28px; }
      }
    </style>
</head>
<body>

<?php include 'includes/icons.php'; ?>

<nav class="landing-nav">
  <div class="brand">
    <div class="brand-mark">GS</div>
    <div class="brand-text">
      <div class="name">GuideSched</div>
      <div class="portal">CAGASAT HIGH SCHOOL</div>
    </div>
  </div>
  <div style="display:flex; gap:10px; align-items:center;">
    <a href="download-app.php" class="btn btn-outline btn-sm" title="Download GuideSched App">
      <span class="icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></span>Download App
    </a>
    <a href="login.php" class="btn btn-ghost btn-sm">Log In</a>
    <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
  </div>
</nav>

<div class="landing-hero">
  <h1>Your Guidance. Your Schedule. Your Well-being.</h1>
  <p>Making guidance counseling accessible, confidential, and flexible for Cagasat High School students & guidance office staff.</p>
  <div style="display:flex; justify-content:center; gap:12px; flex-wrap:wrap;">
    <a href="register.php" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">Book an Appointment</a>
    <button onclick="triggerPWAInstall()" class="btn btn-outline" style="padding: 12px 24px; font-size: 15px; color:#fff; border-color:rgba(255,255,255,0.4);">
      <span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></span>Install App
    </button>
  </div>
</div>

<div class="landing-section">
  <div style="text-align:center;">
    <h2>Why Choose GuideSched?</h2>
    <p style="color:var(--muted); margin-top:6px;">Simplified guidance scheduling built for modern student care.</p>
  </div>

  <div class="feat-grid">
    <div class="feat-card">
      <div class="feat-icon"><svg width="24" height="24"><use href="#i-cal"/></svg></div>
      <h3>Easy Scheduling</h3>
      <p style="color:var(--muted); font-size:13.5px; margin-top:8px;">Choose your preferred guidance counselor, date, and time slot with instant confirmation.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon"><svg width="24" height="24"><use href="#i-bell"/></svg></div>
      <h3>Smart Notifications</h3>
      <p style="color:var(--muted); font-size:13.5px; margin-top:8px;">Receive timely appointment reminders and status updates directly on your dashboard.</p>
    </div>
    <div class="feat-card">
      <div class="feat-icon"><svg width="24" height="24"><use href="#i-shield"/></svg></div>
      <h3>Strict Confidentiality</h3>
      <p style="color:var(--muted); font-size:13.5px; margin-top:8px;">Your appointments and counseling records are kept completely private and secure.</p>
    </div>
  </div>
</div>

<footer style="text-align:center; padding: 24px; border-top: 1px solid var(--line); color: var(--muted); font-size: 13px;">
  &copy; <?php echo date('Y'); ?> GuideSched — Guidance Office, Cagasat High School. All rights reserved. · <a href="download-app.php" style="color:var(--violet-600); text-decoration:none;">Download App</a>
</footer>

</body>
</html>
