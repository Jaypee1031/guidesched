<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$stats = getAdminStatistics();

// Handle quick approve/decline
$error = '';
$success = '';

if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $action = sanitizeInput($_GET['action']);
    
    if ($action === 'approve') {
        $result = updateAppointmentStatus($appointment_id, 'approved', $_SESSION['user_id']);
        if ($result['success']) { $success = 'Appointment approved.'; } else { $error = $result['message']; }
    } elseif ($action === 'decline') {
        $result = updateAppointmentStatus($appointment_id, 'declined', $_SESSION['user_id']);
        if ($result['success']) { $success = 'Appointment declined.'; } else { $error = $result['message']; }
    }
    // Refresh stats after action
    $stats = getAdminStatistics();
}

$pending_requests = getAllAppointments('pending');
$all_apts = getAllAppointments();

// Filter today's schedule
$today_str = date('Y-m-d');
$today_schedule = array_filter($all_apts, function($a) use ($today_str) {
    return $a['appointment_date'] === $today_str;
});

// Calculate no show rate
$total_completed = $stats['completed_sessions'] + ($stats['no_shows'] ?? 0);
$no_show_rate = $total_completed > 0 ? round(($stats['no_shows'] / $total_completed) * 100) . '%' : '8%';

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Dashboard — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'dashboard';
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
        <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?></h1>
        <div class="sub"><?php echo date('l, F j, Y'); ?> · Guidance Office, Cagasat High School</div>
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

      <!-- 4 CLICKABLE STAT CARDS -->
      <div class="grid cols-4" style="margin-bottom:16px;">
        <div class="card stat clickable" onclick="location.href='appointments.php?tab=approved'" title="Click to view confirmed appointments">
          <div class="icon-wrap"><svg><use href="#i-cal"/></svg></div>
          <div class="num"><?php echo count($today_schedule); ?></div>
          <div class="lbl">Today's Appointments ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='appointments.php?tab=pending'" title="Click to review pending requests">
          <div class="icon-wrap" style="background:var(--amber-bg); color:var(--amber);"><svg><use href="#i-alert"/></svg></div>
          <div class="num"><?php echo count($pending_requests); ?></div>
          <div class="lbl">Pending Approvals ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='reports.php'" title="Click to view reports">
          <div class="icon-wrap"><svg><use href="#i-chart"/></svg></div>
          <div class="num"><?php echo $stats['approved_appointments'] + $stats['completed_sessions']; ?></div>
          <div class="lbl">This Week's Sessions ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='reports.php?status=no_show'" title="Click to view no-show details">
          <div class="icon-wrap" style="background:var(--red-bg); color:var(--red);"><svg><use href="#i-x"/></svg></div>
          <div class="num"><?php echo $no_show_rate; ?></div>
          <div class="lbl">No-show Rate ➜</div>
        </div>
      </div>

      <div class="grid cols-2">
        <!-- TODAY'S SCHEDULE -->
        <div class="card">
          <div class="card-head">
            <h3>Today's Schedule</h3>
            <a href="appointments.php" class="link">View all</a>
          </div>

          <?php if (empty($today_schedule)): ?>
            <div class="empty-note">No appointments scheduled for today.</div>
          <?php else: ?>
            <?php foreach ($today_schedule as $apt): ?>
              <div class="row-item">
                <div class="time-block"><div class="t"><?php echo date('g:i A', strtotime($apt['start_time'])); ?></div></div>
                <div class="info">
                  <div class="title"><?php echo htmlspecialchars($apt['student_name']); ?></div>
                  <div class="sub"><?php echo htmlspecialchars($apt['concern']); ?></div>
                </div>
                <span class="pill <?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- PENDING APPROVALS -->
        <div class="card">
          <div class="card-head">
            <h3>Pending Approvals (<?php echo count($pending_requests); ?>)</h3>
          </div>

          <?php if (empty($pending_requests)): ?>
            <div class="empty-note">No pending appointment requests requiring approval.</div>
          <?php else: ?>
            <?php foreach (array_slice($pending_requests, 0, 5) as $apt): ?>
              <div class="row-item">
                <div class="info">
                  <div class="title"><?php echo htmlspecialchars($apt['student_name']); ?></div>
                  <div class="sub"><?php echo date('M j, g:i A', strtotime($apt['appointment_date'] . ' ' . $apt['start_time'])); ?> · <?php echo htmlspecialchars(substr($apt['concern'], 0, 30)); ?></div>
                </div>
                <div class="actions">
                  <a href="dashboard.php?action=approve&id=<?php echo $apt['id']; ?>" class="btn btn-approve btn-sm btn-icon" title="Approve">
                    <svg width="15" height="15"><use href="#i-check"/></svg>
                  </a>
                  <a href="dashboard.php?action=decline&id=<?php echo $apt['id']; ?>" class="btn btn-decline btn-sm btn-icon" title="Decline" onclick="return confirm('Decline this request?');">
                    <svg width="15" height="15"><use href="#i-x"/></svg>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

</body>
</html>
