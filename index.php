<?php
require_once 'config/config.php';
$page_title = 'GuideSched — Guidance Counseling System';
$base_url_path = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <style>
      .landing-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 40px;
        background: #fff;
        border-bottom: 1px solid var(--line);
      }
      .landing-hero {
        padding: 80px 20px;
        text-align: center;
        background: linear-gradient(135deg, var(--violet-950), var(--violet-700));
        color: #fff;
      }
      .landing-hero h1 { color: #fff; font-size: 38px; margin-bottom: 16px; }
      .landing-hero p { color: #E4D8FC; font-size: 17px; max-width: 600px; margin: 0 auto 28px; }
      .landing-section { padding: 60px 40px; max-width: 1100px; margin: 0 auto; }
      .feat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 30px; }
      @media(max-width: 768px) { .feat-grid { grid-template-columns: 1fr; } }
      .feat-card { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 24px; text-align: center; box-shadow: var(--shadow); }
      .feat-icon { width: 48px; height: 48px; border-radius: 12px; background: var(--violet-100); color: var(--violet-600); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
    </style>
</head>
<body>

<?php include 'includes/icons.php'; ?>

<nav class="landing-nav">
  <div class="brand">
    <div class="brand-mark">GS</div>
    <div class="brand-text">
      <div class="name">GuideSched</div>
      <div class="portal">QSU DIFFUN CAMPUS</div>
    </div>
  </div>
  <div style="display:flex; gap:12px;">
    <a href="login.php" class="btn btn-outline">Log In</a>
    <a href="register.php" class="btn btn-primary">Sign Up</a>
  </div>
</nav>

<div class="landing-hero">
  <h1>Your Guidance. Your Schedule. Your Well-being.</h1>
  <p>Making guidance counseling more accessible, confidential, and flexible for QSU Diffun students.</p>
  <a href="register.php" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px;">Book an Appointment</a>
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
      <p style="color:var(--muted); font-size:13.5px; margin-top:8px;">Choose your preferred counselor, date, and time slot with instant confirmation.</p>
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
  &copy; <?php echo date('Y'); ?> GuideSched — Guidance Office, QSU Diffun Campus. All rights reserved.
</footer>

</body>
</html>
