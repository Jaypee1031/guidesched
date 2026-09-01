<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

$user = getUserProfile($_SESSION['user_id']);
$stats = getStudentAppointmentStats($_SESSION['user_id']);
$all_apts = getStudentAppointments($_SESSION['user_id']);

$last_visit = 'None yet';
foreach ($all_apts as $apt) {
    if ($apt['status'] === 'completed') {
        $last_visit = date('M j, Y', strtotime($apt['appointment_date']));
        break;
    }
}

$counselor_name = 'Ms. Grace Fontanilla';
if (!empty($all_apts) && !empty($all_apts[0]['counselor_name'])) {
    $counselor_name = $all_apts[0]['counselor_name'];
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $dept_grade = sanitizeInput($_POST['course']);
        $year_lvl = 7;
        if (strpos($dept_grade, 'Grade 8') !== false) { $year_lvl = 8; }
        elseif (strpos($dept_grade, 'Grade 9') !== false) { $year_lvl = 9; }
        elseif (strpos($dept_grade, 'Grade 10') !== false) { $year_lvl = 10; }
        elseif (strpos($dept_grade, 'Grade 11') !== false) { $year_lvl = 11; }
        elseif (strpos($dept_grade, 'Grade 12') !== false) { $year_lvl = 12; }

        $data = [
            'name' => sanitizeInput($_POST['name']),
            'email' => sanitizeInput($_POST['email']),
            'student_number' => sanitizeInput($_POST['student_number']),
            'course' => $dept_grade,
            'year_level' => $year_lvl,
            'contact_number' => sanitizeInput($_POST['contact_number'])
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

$unread_count = count(getStudentNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'My Profile — GuideSched — Cagasat High School';
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
  <?php include '../includes/student_sidebar.php'; ?>

  <div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <h1>My Profile</h1>
        <div class="sub">Your personal information and credentials</div>
      </div>
      <div class="topbar-right">
        <a href="notifications.php" class="bell-btn">
          <?php if ($unread_count > 0): ?><span class="bell-dot"></span><?php endif; ?>
          <span class="icon"><svg><use href="#i-bell"/></svg></span>
        </a>
        <a href="profile.php" class="topbar-user-badge" title="Click to view My Profile">
          <div class="avatar"><?php echo $user_initials; ?></div>
          <div class="user-meta">
            <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
            <span class="user-role"><?php echo htmlspecialchars($user['course'] ?? 'Student'); ?></span>
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
        <!-- LEFT: EDIT PROFILE FORM -->
        <div class="card">
          <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
            <div class="avatar" style="width:64px;height:64px;font-size:20px;">
              <?php echo $user_initials; ?>
            </div>
            <div>
              <h3 style="font-size:17px;"><?php echo htmlspecialchars($user['name']); ?></h3>
              <div class="sub" style="color:var(--muted);font-size:13px;">Student LRN / ID: <?php echo htmlspecialchars($user['student_number']); ?></div>
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
                <label>LRN / Student ID</label>
                <input type="text" name="student_number" value="<?php echo htmlspecialchars($user['student_number']); ?>" required>
              </div>
            </div>

            <div class="field">
              <label>Department & Grade Level / Strand (Selectable)</label>
              <select name="course" required>
                <optgroup label="Junior High School Department">
                  <option value="Grade 7 (Junior High)" <?php echo ($user['course'] ?? '') === 'Grade 7 (Junior High)' ? 'selected' : ''; ?>>Grade 7 (Junior High)</option>
                  <option value="Grade 8 (Junior High)" <?php echo ($user['course'] ?? '') === 'Grade 8 (Junior High)' ? 'selected' : ''; ?>>Grade 8 (Junior High)</option>
                  <option value="Grade 9 (Junior High)" <?php echo ($user['course'] ?? '') === 'Grade 9 (Junior High)' ? 'selected' : ''; ?>>Grade 9 (Junior High)</option>
                  <option value="Grade 10 (Junior High)" <?php echo ($user['course'] ?? '') === 'Grade 10 (Junior High)' ? 'selected' : ''; ?>>Grade 10 (Junior High)</option>
                </optgroup>
                <optgroup label="Senior High School Department">
                  <option value="Grade 11 - STEM" <?php echo ($user['course'] ?? '') === 'Grade 11 - STEM' ? 'selected' : ''; ?>>Grade 11 - STEM</option>
                  <option value="Grade 11 - ABM" <?php echo ($user['course'] ?? '') === 'Grade 11 - ABM' ? 'selected' : ''; ?>>Grade 11 - ABM</option>
                  <option value="Grade 11 - HUMSS" <?php echo ($user['course'] ?? '') === 'Grade 11 - HUMSS' ? 'selected' : ''; ?>>Grade 11 - HUMSS</option>
                  <option value="Grade 11 - TVL" <?php echo ($user['course'] ?? '') === 'Grade 11 - TVL' ? 'selected' : ''; ?>>Grade 11 - TVL</option>
                  <option value="Grade 12 - STEM" <?php echo ($user['course'] ?? '') === 'Grade 12 - STEM' ? 'selected' : ''; ?>>Grade 12 - STEM</option>
                  <option value="Grade 12 - ABM" <?php echo ($user['course'] ?? '') === 'Grade 12 - ABM' ? 'selected' : ''; ?>>Grade 12 - ABM</option>
                  <option value="Grade 12 - HUMSS" <?php echo ($user['course'] ?? '') === 'Grade 12 - HUMSS' ? 'selected' : ''; ?>>Grade 12 - HUMSS</option>
                  <option value="Grade 12 - TVL" <?php echo ($user['course'] ?? '') === 'Grade 12 - TVL' ? 'selected' : ''; ?>>Grade 12 - TVL</option>
                </optgroup>
              </select>
            </div>

            <div class="form-grid">
              <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
              </div>
              <div class="field">
                <label>Contact Number</label>
                <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>
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

        <!-- RIGHT: SUMMARY & PRIVACY NOTE -->
        <div class="card">
          <h3 style="margin-bottom:14px;">Appointment Summary</h3>
          <div style="display:flex; flex-direction:column; gap:12px;">
            <div class="row-item" style="padding:0 0 12px;">
              <div class="info"><div class="title">Total sessions</div></div>
              <div style="margin-left:auto; font-family:'Sora'; font-weight:700; color:var(--violet-700);"><?php echo $stats['total']; ?></div>
            </div>
            <div class="row-item" style="padding:0 0 12px;">
              <div class="info"><div class="title">Last visit</div></div>
              <div style="margin-left:auto; font-weight:700;"><?php echo $last_visit; ?></div>
            </div>
            <div class="row-item" style="padding:0; border-bottom:none;">
              <div class="info"><div class="title">Assigned counselor</div></div>
              <div style="margin-left:auto; font-weight:700;"><?php echo htmlspecialchars($counselor_name); ?></div>
            </div>
          </div>

          <div class="quote-card" style="margin-top:18px;">
            <p class="q" style="font-size:13px;">
              <span class="icon" style="display:inline-block;vertical-align:-4px;margin-right:6px;"><svg width="16" height="16"><use href="#i-shield"/></svg></span>
              Your counseling records are kept confidential and are never shared without your consent.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
