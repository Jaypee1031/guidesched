<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$error = '';
$success = '';

// Handle slot creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_slot'])) {
    $counselor_id = intval($_POST['counselor_id']);
    $date = sanitizeInput($_POST['date']);
    $start_time = sanitizeInput($_POST['start_time']);
    $end_time = sanitizeInput($_POST['end_time']);
    
    $result = createAvailabilitySlot($counselor_id, $date, $start_time, $end_time);
    if ($result['success']) { $success = $result['message']; } else { $error = $result['message']; }
}

// Handle slot deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $slot_id = intval($_GET['delete']);
    $result = deleteAvailabilitySlot($slot_id);
    if ($result['success']) { $success = $result['message']; } else { $error = $result['message']; }
}

$counselor_filter = isset($_GET['counselor']) && is_numeric($_GET['counselor']) ? intval($_GET['counselor']) : null;
$counselors = getAvailableCounselors();

// Default to first counselor if none selected
if (!$counselor_filter && !empty($counselors)) {
    $counselor_filter = $counselors[0]['id'];
}

$availability = [];
if ($counselor_filter) {
    $availability = getCounselorAvailabilityAdmin($counselor_filter);
}

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Schedule Management — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'schedule';
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
        <h1>Schedule Management</h1>
        <div class="sub">Set counselor availability and manage time slots</div>
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

      <!-- CREATE TIME SLOT CARD -->
      <div class="card" style="margin-bottom:20px;">
        <h3 style="margin-bottom:14px;">Create Availability Time Slot</h3>
        <form method="POST" action="">
          <input type="hidden" name="create_slot" value="1">
          <div class="form-grid">
            <div class="field">
              <label>Counselor</label>
              <select name="counselor_id" required>
                <?php foreach ($counselors as $c): ?>
                  <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Date</label>
              <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="field">
              <label>Start Time</label>
              <input type="time" name="start_time" value="09:00" required>
            </div>
            <div class="field">
              <label>End Time</label>
              <input type="time" name="end_time" value="10:00" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:6px;">
            <span class="icon"><svg><use href="#i-plus"/></svg></span>Create Slot
          </button>
        </form>
      </div>

      <!-- FILTER & SLOTS LIST CARD -->
      <div class="card">
        <div class="card-head">
          <h3>Counselor Time Slots</h3>
          <form method="GET" action="" style="display:flex; gap:10px; align-items:center;">
            <label style="font-size:12px; font-weight:700; color:var(--muted);">Filter Counselor:</label>
            <select name="counselor" onchange="this.form.submit()" style="padding:6px 12px; border-radius:8px; border:1px solid var(--line); font-size:13px;">
              <?php foreach ($counselors as $c): ?>
                <option value="<?php echo $c['id']; ?>" <?php echo $counselor_filter == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>

        <?php if (empty($availability)): ?>
          <div class="empty-note">No availability slots found for this counselor. Create a slot above.</div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($availability as $slot): ?>
                <tr>
                  <td><?php echo date('M j, Y (D)', strtotime($slot['date'])); ?></td>
                  <td><?php echo date('g:i A', strtotime($slot['start_time'])); ?></td>
                  <td><?php echo date('g:i A', strtotime($slot['end_time'])); ?></td>
                  <td><span class="pill <?php echo $slot['status']; ?>"><?php echo ucfirst($slot['status']); ?></span></td>
                  <td>
                    <?php if ($slot['status'] !== 'booked'): ?>
                      <a href="schedule.php?delete=<?php echo $slot['id']; ?>&counselor=<?php echo $counselor_filter; ?>" class="btn btn-decline btn-sm" onclick="return confirm('Delete this slot?');">Delete</a>
                    <?php else: ?>
                      <span style="font-size:12px; color:var(--faint);">Booked</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

</body>
</html>
