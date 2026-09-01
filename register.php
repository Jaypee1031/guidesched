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
        $department_grade = sanitizeInput($_POST['department_grade']);
        
        // Extract numeric year level from grade choice
        $year_level = 7;
        if (strpos($department_grade, 'Grade 8') !== false) { $year_level = 8; }
        elseif (strpos($department_grade, 'Grade 9') !== false) { $year_level = 9; }
        elseif (strpos($department_grade, 'Grade 10') !== false) { $year_level = 10; }
        elseif (strpos($department_grade, 'Grade 11') !== false) { $year_level = 11; }
        elseif (strpos($department_grade, 'Grade 12') !== false) { $year_level = 12; }

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
                'course' => $department_grade,
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

$page_title = 'Student Sign Up — GuideSched — Cagasat High School';
$base_url_path = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="authpage active" id="page-signup">
  <div class="auth-card" style="max-width:480px;">
    <div class="brand">
      <div class="brand-mark">GS</div>
      <div class="brand-text">
        <div class="name">GuideSched</div>
        <div class="portal">CAGASAT HIGH SCHOOL</div>
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
          <label>LRN / Student ID</label>
          <input type="text" name="student_number" placeholder="102938475601" required value="<?php echo isset($_POST['student_number']) ? htmlspecialchars($_POST['student_number']) : ''; ?>">
        </div>
        <div class="field">
          <label>Contact Number</label>
          <input type="text" name="contact_number" placeholder="0917 123 4567" required value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>">
        </div>
      </div>

      <div class="field">
        <label>Department & Grade Level / Strand</label>
        <select name="department_grade" required>
          <optgroup label="Junior High School Department">
            <option value="Grade 7 (Junior High)">Grade 7 (Junior High)</option>
            <option value="Grade 8 (Junior High)">Grade 8 (Junior High)</option>
            <option value="Grade 9 (Junior High)">Grade 9 (Junior High)</option>
            <option value="Grade 10 (Junior High)">Grade 10 (Junior High)</option>
          </optgroup>
          <optgroup label="Senior High School Department">
            <option value="Grade 11 - STEM" selected>Grade 11 - STEM</option>
            <option value="Grade 11 - ABM">Grade 11 - ABM</option>
            <option value="Grade 11 - HUMSS">Grade 11 - HUMSS</option>
            <option value="Grade 11 - TVL">Grade 11 - TVL</option>
            <option value="Grade 12 - STEM">Grade 12 - STEM</option>
            <option value="Grade 12 - ABM">Grade 12 - ABM</option>
            <option value="Grade 12 - HUMSS">Grade 12 - HUMSS</option>
            <option value="Grade 12 - TVL">Grade 12 - TVL</option>
          </optgroup>
        </select>
      </div>

      <div class="field">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="you@cagasaths.edu.ph" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
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
