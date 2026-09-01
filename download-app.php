<?php
require_once 'config/config.php';

$page_title = 'Download App — GuideSched — Cagasat High School';
$base_url_path = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
    <style>
      .app-hero {
        background: linear-gradient(135deg, var(--violet-950) 0%, var(--violet-700) 100%);
        color: #fff;
        padding: 60px 24px;
        text-align: center;
        border-radius: 0 0 24px 24px;
        position: relative;
        overflow: hidden;
      }
      .app-hero h1 {
        font-family: 'Sora', sans-serif;
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 12px;
      }
      .app-hero p {
        font-size: 15px;
        color: var(--violet-100);
        max-width: 560px;
        margin: 0 auto 24px;
        line-height: 1.6;
      }
      .app-badge-grid {
        display: flex;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
      }
      .step-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 24px;
        text-align: center;
        transition: transform 0.2s ease;
      }
      .step-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow);
      }
      .step-num {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--violet-100);
        color: var(--violet-700);
        font-weight: 800;
        font-size: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
      }
    </style>
</head>
<body>

<?php include 'includes/icons.php'; ?>

<!-- NAV HEADER -->
<div style="background:#fff; border-bottom:1px solid var(--line); padding:14px 24px;">
  <div style="max-width:1100px; margin:0 auto; display:flex; align-items:center; justify-content:space-between;">
    <a href="index.php" style="display:flex; align-items:center; gap:10px; text-decoration:none;">
      <div style="width:34px; height:34px; background:var(--violet-600); color:#fff; font-family:'Sora'; font-weight:800; border-radius:8px; display:flex; align-items:center; justify-content:center;">GS</div>
      <div>
        <div style="font-family:'Sora'; font-weight:700; font-size:16px; color:var(--violet-950);">GuideSched</div>
        <div style="font-size:10px; color:var(--muted); font-weight:600;">CAGASAT HIGH SCHOOL</div>
      </div>
    </a>
    <div>
      <a href="login.php" class="btn btn-outline btn-sm">Log In</a>
      <a href="register.php" class="btn btn-primary btn-sm" style="margin-left:8px;">Sign Up</a>
    </div>
  </div>
</div>

<!-- APP HERO -->
<div class="app-hero">
  <div style="width:72px; height:72px; background:rgba(255,255,255,0.15); border-radius:20px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;">
    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
  </div>
  <h1>Download GuideSched Mobile & Desktop App</h1>
  <p>Access appointment scheduling, guidance notifications, and confidential counseling directly from your home screen — fast, lightweight, and offline-enabled!</p>
  
  <div class="app-badge-grid">
    <button onclick="triggerPWAInstall()" class="btn btn-primary" style="padding:12px 28px; font-size:15px;">
      <span class="icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></span>
      Install App Now
    </button>
  </div>
</div>

<!-- INSTALLATION STEPS -->
<div style="max-width:1100px; margin:40px auto; padding:0 24px;">
  <h2 style="font-family:'Sora'; text-align:center; font-size:24px; color:var(--violet-950); margin-bottom:8px;">How to Install on Your Device</h2>
  <p style="text-align:center; color:var(--muted); font-size:14px; margin-bottom:32px;">Choose your platform below to add GuideSched to your Home Screen in 3 seconds.</p>

  <div class="grid cols-3">
    <!-- ANDROID -->
    <div class="step-card">
      <div class="step-num">🤖</div>
      <h3 style="font-size:16px; margin-bottom:8px;">Android Phone</h3>
      <p style="font-size:13px; color:var(--muted); line-height:1.5;">
        Open GuideSched in Google Chrome, tap the <strong>three dots menu (⋮)</strong> at top-right, then select <strong>"Add to Home screen"</strong> or <strong>"Install app"</strong>.
      </p>
    </div>

    <!-- IPHONE / IOS -->
    <div class="step-card">
      <div class="step-num">🍎</div>
      <h3 style="font-size:16px; margin-bottom:8px;">iPhone / iPad</h3>
      <p style="font-size:13px; color:var(--muted); line-height:1.5;">
        Open GuideSched in Safari, tap the <strong>Share button</strong> at the bottom bar, scroll down, and tap <strong>"Add to Home Screen"</strong>.
      </p>
    </div>

    <!-- WINDOWS / MAC -->
    <div class="step-card">
      <div class="step-num">💻</div>
      <h3 style="font-size:16px; margin-bottom:8px;">Windows & Mac</h3>
      <p style="font-size:13px; color:var(--muted); line-height:1.5;">
        Click the <strong>Install icon</strong> in Chrome/Edge address bar, or click <strong>"Install App Now"</strong> above to launch GuideSched as a desktop program.
      </p>
    </div>
  </div>

  <!-- APP FEATURES LIST -->
  <div style="margin-top:48px; background:#fff; border:1px solid var(--line); border-radius:20px; padding:32px;">
    <h3 style="font-family:'Sora'; font-size:20px; color:var(--violet-950); margin-bottom:18px;">Why Download the App?</h3>
    <div class="grid cols-3">
      <div>
        <div style="font-weight:700; color:var(--violet-700); margin-bottom:4px;">⚡ Instant 1-Click Launch</div>
        <div style="font-size:13px; color:var(--muted);">No need to type URLs. Launch directly from your phone app icon.</div>
      </div>
      <div>
        <div style="font-weight:700; color:var(--violet-700); margin-bottom:4px;">🔔 Real-time Notifications</div>
        <div style="font-size:13px; color:var(--muted);">Get instant appointment updates, approvals, and reminder notifications.</div>
      </div>
      <div>
        <div style="font-weight:700; color:var(--violet-700); margin-bottom:4px;">🔒 Private & Confidential</div>
        <div style="font-size:13px; color:var(--muted);">Fast biometric / stored login session for Cagasat High School students.</div>
      </div>
    </div>
  </div>
</div>

<footer style="text-align:center; padding:24px; color:var(--muted); font-size:12px; border-top:1px solid var(--line); margin-top:40px;">
  GuideSched App — Cagasat High School Guidance Counseling Office
</footer>

</body>
</html>
