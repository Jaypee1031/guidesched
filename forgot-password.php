<?php
require_once 'config/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    if ($email) {
        $message = 'If an account exists for ' . htmlspecialchars($email) . ', a password reset link has been sent to your email.';
    } else {
        $error = 'Please enter a valid email address.';
    }
}

$page_title = 'Reset Password — GuideSched';
$base_url_path = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="authpage active" id="page-forgot">
  <div class="auth-card">
    <div class="brand">
      <div class="brand-mark">GS</div>
      <div class="brand-text">
        <div class="name">GuideSched</div>
        <div class="portal">QSU DIFFUN CAMPUS</div>
      </div>
    </div>
    <h2>Reset your password</h2>
    <div class="auth-sub">Enter your email and we'll send you a reset link</div>

    <?php if ($message): ?>
      <div class="alert-box alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert-box alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="field" style="margin-bottom:8px;">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@qsu.edu.ph" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:14px;">Send Reset Link</button>
    </form>

    <div class="foot-note">
      <a href="login.php" class="link-btn">Back to Log In</a>
    </div>
  </div>
</div>

</body>
</html>
