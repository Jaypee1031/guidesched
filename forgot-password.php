<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email']);
        
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $success = 'If an account exists with that email, password reset instructions have been sent. Please contact the Guidance Office.';
        } else {
            $success = 'If an account exists with that email, password reset instructions have been sent.';
        }
        closeDBConnection($conn);
    }
}

$page_title = 'Forgot Password — GuideSched — Cagasat High School';
$base_url_path = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="authpage">
  <div class="auth-card">
    <div class="brand">
      <div class="brand-mark">GS</div>
      <div class="brand-text">
        <div class="name">GuideSched</div>
        <div class="portal">CAGASAT HIGH SCHOOL</div>
      </div>
    </div>
    <h2>Reset Password</h2>
    <div class="auth-sub">Enter your email to receive password reset instructions</div>

    <?php if ($error): ?>
      <div class="alert-box alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-box alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?php addCSRFToken(); ?>
      <div class="field" style="margin-bottom:18px;">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@cagasaths.edu.ph" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Send Reset Link</button>
    </form>

    <div class="foot-note">
      Remember your password? <a href="login.php" class="link-btn">Log in</a>
    </div>
  </div>
</div>

</body>
</html>
