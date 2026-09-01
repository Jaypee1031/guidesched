<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$stats = getAdminStatistics();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'email' => sanitizeInput($_POST['email']),
            'specialization' => sanitizeInput($_POST['specialization'] ?? 'Guidance Counselor'),
            'contact_number' => sanitizeInput($_POST['contact_number'] ?? 'N/A')
        ];
        
        $result = updateUserProfile($_SESSION['user_id'], $data);
        if ($result['success']) {
            $success = $result['message'];
            $user = getUserProfile($_SESSION['user_id']);
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            $result = changePassword($_SESSION['user_id'], $current_password, $new_password);
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'My Profile — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'profile';
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
        <h1>My Profile</h1>
        <div class="sub">Counselor information and schedule preferences</div>
      </div>
      <div class="topbar-right">
        <a href="notifications.php" class="bell-btn">
          <?php if ($unread_count > 0): ?><span class="bell-dot"></span><?php endif; ?>
          <span class="icon"><svg><use href="#i-bell"/></svg></span>
        </a>
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
        <div class="alert-box alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <div class="grid cols-2">
        <!-- LEFT: COUNSELOR DETAILS FORM -->
        <div class="card">
          <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
            <div class="avatar" style="width:64px;height:64px;font-size:20px;background:var(--violet-700);">
              <?php echo $user_initials; ?>
            </div>
            <div>
              <h3 style="font-size:17px;"><?php echo htmlspecialchars($user['name']); ?></h3>
              <div class="sub" style="color:var(--muted);font-size:13px;"><?php echo htmlspecialchars($user['specialization'] ?? 'Guidance Counselor'); ?> · Guidance Office</div>
            </div>
          </div>

          <form method="POST" action="">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-grid">
              <div class="field">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
              </div>
              <div class="field">
                <label>Title / Specialization (Selectable)</label>
                <select name="specialization" required>
                  <option value="Academic Counseling" <?php echo ($user['specialization'] ?? '') === 'Academic Counseling' ? 'selected' : ''; ?>>Academic Counseling</option>
                  <option value="Career & Strand Guidance" <?php echo ($user['specialization'] ?? '') === 'Career & Strand Guidance' ? 'selected' : ''; ?>>Career & Strand Guidance</option>
                  <option value="Behavioral & Emotional Wellness" <?php echo ($user['specialization'] ?? '') === 'Behavioral & Emotional Wellness' ? 'selected' : ''; ?>>Behavioral & Emotional Wellness</option>
                  <option value="Personal & Crisis Counseling" <?php echo ($user['specialization'] ?? '') === 'Personal & Crisis Counseling' ? 'selected' : ''; ?>>Personal & Crisis Counseling</option>
                </select>
              </div>
              <div class="field">
                <label>Department / Institution</label>
                <input type="text" value="Guidance Office — Cagasat High School" disabled>
              </div>
              <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
              </div>
              <div class="field">
                <label>Office Hours</label>
                <input type="text" value="Mon–Fri, 8:00 AM – 5:00 PM" disabled>
              </div>
              <div class="field">
                <label>Room Location</label>
                <input type="text" value="Guidance Office — Room 102" disabled>
              </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:10px;">Save Profile Changes</button>
          </form>

          <hr style="border:none; border-top:1px solid var(--line); margin: 24px 0;">

          <h3 style="font-size:15px; margin-bottom:14px;">Change Password</h3>
          <form method="POST" action="">
            <input type="hidden" name="change_password" value="1">
            <div class="field">
              <label>Current Password</label>
              <input type="password" name="current_password" required>
            </div>
            <div class="form-grid">
              <div class="field">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6">
              </div>
              <div class="field">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6">
              </div>
            </div>
            <button type="submit" class="btn btn-outline" style="margin-top:6px;">Update Password</button>
          </form>
        </div>

        <!-- RIGHT: WEEK'S OVERVIEW -->
        <div class="card">
          <h3 style="margin-bottom:14px;">This Week's Overview</h3>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div class="row-item" style="padding:0 0 12px;">
              <div class="info"><div class="title">Sessions completed</div></div>
              <div style="margin-left:auto; font-family:'Sora'; font-weight:700; color:var(--violet-700);"><?php echo $stats['completed_sessions']; ?></div>
            </div>
            <div class="row-item" style="padding:0 0 12px;">
              <div class="info"><div class="title">Pending approvals</div></div>
              <div style="margin-left:auto; font-weight:700;"><?php echo $stats['pending_requests']; ?></div>
            </div>
            <div class="row-item" style="padding:0; border-bottom:none;">
              <div class="info"><div class="title">Open slots remaining</div></div>
              <div style="margin-left:auto; font-weight:700;">9</div>
            </div>
          </div>

          <div class="quote-card" style="margin-top:18px;">
            <p class="q" style="font-size:13px;">
              <span class="icon" style="display:inline-block;vertical-align:-4px;margin-right:6px;"><svg width="16" height="16"><use href="#i-shield"/></svg></span>
              Student records remain confidential and are visible only to assigned guidance counselors.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
