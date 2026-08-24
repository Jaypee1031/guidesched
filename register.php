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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $name = sanitizeInput($_POST['name']);
        $student_number = sanitizeInput($_POST['student_number']);
        $course = sanitizeInput($_POST['course']);
        $year_level = intval($_POST['year_level'] ?? 1);
        $email = sanitizeInput($_POST['email']);
        $contact_number = sanitizeInput($_POST['contact_number'] ?? 'N/A');
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
        } else {
            $data = [
                'name' => $name,
                'student_number' => $student_number,
                'course' => $course,
                'year_level' => $year_level,
                'email' => $email,
                'contact_number' => $contact_number,
                'password' => $password
            ];
            
            $result = registerStudent($data);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

$page_title = 'Student Sign Up — GuideSched';
$base_url_path = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="authpage active" id="page-signup">
  <div class="auth-card" style="max-width:460px;">
    <div class="brand">
      <div class="brand-mark">GS</div>
      <div class="brand-text">
        <div class="name">GuideSched</div>
        <div class="portal">STUDENT SIGN UP</div>
      </div>
    </div>
    <h2>Create your account</h2>
    <div class="auth-sub">Register to book and manage guidance appointments</div>

    <?php if ($error): ?>
      <div class="alert-box alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert-box alert-success">
        <?php echo htmlspecialchars($success); ?>
        <div style="margin-top:10px;">
          <a href="login.php" class="btn btn-primary btn-sm">Proceed to Login</a>
        </div>
      </div>
    <?php else: ?>

    <form method="POST" action="">
      <?php addCSRFToken(); ?>
      <div class="field">
        <label>Full Name</label>
        <input type="text" name="name" placeholder="Aira Delos Santos" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
      </div>
      <div class="form-grid">
        <div class="field">
          <label>Student ID</label>
          <input type="text" name="student_number" placeholder="2023-0000-DF" required value="<?php echo isset($_POST['student_number']) ? htmlspecialchars($_POST['student_number']) : ''; ?>">
        </div>
        <div class="field">
          <label>Year Level</label>
          <select name="year_level" required>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3" selected>3rd Year</option>
            <option value="4">4th Year</option>
            <option value="5">5th Year</option>
          </select>
        </div>
      </div>
      <div class="form-grid">
        <div class="field">
          <label>Course / Program</label>
          <input type="text" name="course" placeholder="BSIT" required value="<?php echo isset($_POST['course']) ? htmlspecialchars($_POST['course']) : ''; ?>">
        </div>
        <div class="field">
          <label>Contact Number</label>
          <input type="text" name="contact_number" placeholder="0917 123 4567" required value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>">
        </div>
      </div>
      <div class="field">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@qsu.edu.ph" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
      </div>
      <div class="form-grid">
        <div class="field">
          <label>Password</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="field">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:10px;">Create Account</button>
    </form>

    <?php endif; ?>

    <div class="foot-note">
      Already have an account? <a href="login.php" class="link-btn">Log in</a>
    </div>
  </div>
</div>

</body>
</html>
