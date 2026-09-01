<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireRole('admin');

$user = getUserProfile($_SESSION['user_id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => sanitizeInput($_POST['name']),
        'email' => sanitizeInput($_POST['email']),
        'password' => $_POST['password'],
        'specialization' => sanitizeInput($_POST['specialization']),
        'contact_number' => sanitizeInput($_POST['contact_number'])
    ];
    
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = 'Passwords do not match.';
    } else {
        $result = addCounselor($data);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Add Counselor — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'counselors';
$base_url_path = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/head.php'; ?>
</head>
<body>

<?php include '../includes/icons.php'; ?>

<div class="app">
  <?php include '../includes/admin_sidebar.php'; ?>

  <div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <h1>Add Counselor</h1>
        <div class="sub">Register a new guidance counselor account</div>
      </div>
      <div class="topbar-right">
        <a href="counselors.php" class="btn btn-outline">Back to Counselors</a>
        <a href="profile.php" class="topbar-user-badge" title="Click to view My Profile">
          <div class="avatar" style="background:var(--violet-700);"><?php echo $user_initials; ?></div>
          <div class="user-meta">
            <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
            <span class="user-role"><?php echo htmlspecialchars($user['specialization'] ?? ucfirst($_SESSION['role'])); ?></span>
          </div>
        </a>
      </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content">
      <?php if ($error): ?>
        <div class="alert-box alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert-box alert-success">
          <?php echo htmlspecialchars($success); ?>
          <div style="margin-top:10px;">
            <a href="counselors.php" class="btn btn-primary btn-sm">Return to Counselors List</a>
          </div>
        </div>
      <?php else: ?>

      <div class="card" style="max-width:680px;">
        <h3 style="margin-bottom:16px;">Counselor Registration Form</h3>
        <form method="POST" action="">
          <div class="form-grid">
            <div class="field">
              <label>Full Name</label>
              <input type="text" name="name" required placeholder="Dr. Grace Fontanilla">
            </div>
            <div class="field">
              <label>Email Address</label>
              <input type="email" name="email" required placeholder="g.fontanilla@cagasaths.edu.ph">
            </div>
            <div class="field">
              <label>Specialization (Selectable)</label>
              <select name="specialization" required>
                <option value="Academic Counseling">Academic Counseling</option>
                <option value="Career & Strand Guidance" selected>Career & Strand Guidance</option>
                <option value="Behavioral & Emotional Wellness">Behavioral & Emotional Wellness</option>
                <option value="Personal & Crisis Counseling">Personal & Crisis Counseling</option>
              </select>
            </div>
            <div class="field">
              <label>Contact Number</label>
              <input type="text" name="contact_number" required placeholder="0917 123 4567">
            </div>
            <div class="field">
              <label>Password</label>
              <input type="password" name="password" required minlength="6">
            </div>
            <div class="field">
              <label>Confirm Password</label>
              <input type="password" name="confirm_password" required minlength="6">
            </div>
          </div>
          <div style="display:flex; gap:10px; margin-top:14px;">
            <button type="submit" class="btn btn-primary">Add Counselor</button>
            <a href="counselors.php" class="btn btn-outline">Cancel</a>
          </div>
        </form>
      </div>

      <?php endif; ?>
    </div>
  </div>
</div>

</body>
</html>
