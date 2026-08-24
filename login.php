<?php
require_once 'config/config.php';
require_once 'includes/auth_functions.php';

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    $role = getUserRole();
    if ($role === 'student') {
        redirect('student/dashboard.php');
    } elseif ($role === 'counselor' || $role === 'admin') {
        redirect('admin/dashboard.php');
    }
}

$error = '';
$success = '';

// Check for session expired message
if (isset($_GET['session_expired']) && $_GET['session_expired'] === 'true') {
    $error = 'Your session has expired. Please login again.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        if (!checkRateLimit($_SERVER['REMOTE_ADDR'] ?? 'unknown', 5, 300)) {
            $error = 'Too many login attempts. Please try again later.';
        } else {
            $email = sanitizeInput($_POST['email']);
            $password = $_POST['password'];
            
            $result = loginUser($email, $password);
            
            if ($result['success']) {
                if ($result['role'] === 'student') {
                    redirect('student/dashboard.php');
                } elseif ($result['role'] === 'counselor' || $result['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                }
            } else {
                $error = $result['message'];
                recordRateLimitAttempt($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            }
        }
    }
}

$page_title = 'Login — GuideSched';
$base_url_path = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="authpage active" id="page-login">
  <div class="auth-card">
    <div class="brand">
      <div class="brand-mark">GS</div>
      <div class="brand-text">
        <div class="name">GuideSched</div>
        <div class="portal">QSU DIFFUN CAMPUS</div>
      </div>
    </div>
    <h2>Welcome back</h2>
    <div class="auth-sub">Log in to continue to your account</div>

    <?php if ($error): ?>
      <div class="alert-box alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-box alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <?php addCSRFToken(); ?>
      <div class="field">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@qsu.edu.ph" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      </div>
      <div class="field" style="margin-bottom:8px;">
        <label>Password</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <div style="text-align:right; margin-bottom:18px;">
        <a href="forgot-password.php" class="link-btn">Forgot password?</a>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Log In</button>
    </form>

    <div class="foot-note">
      Don't have an account? <a href="register.php" class="link-btn">Sign up</a>
    </div>
  </div>
</div>

</body>
</html>
