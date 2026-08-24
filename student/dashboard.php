<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

$user = getUserProfile($_SESSION['user_id']);
$stats = getStudentAppointmentStats($_SESSION['user_id']);

// Get upcoming appointments
$all_apts = getStudentAppointments($_SESSION['user_id']);
$upcoming = null;
foreach ($all_apts as $apt) {
    if (in_array($apt['status'], ['approved', 'pending']) && strtotime($apt['appointment_date']) >= strtotime(date('Y-m-d'))) {
        $upcoming = $apt;
        break;
    }
}

// Get recent notifications
$recent_notifications = array_slice(getStudentNotifications($_SESSION['user_id']), 0, 3);
$unread_count = count(getStudentNotifications($_SESSION['user_id'], true));

$page_title = 'Home — Student Portal — GuideSched';
$active_page = 'home';
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
        <h1>Welcome, <?php echo htmlspecialchars(explode(' ', trim($user['name']))[0]); ?></h1>
        <div class="sub"><?php echo date('l, F j, Y'); ?> · QSU Diffun Guidance Office</div>
      </div>
      <div class="topbar-right">
        <a href="notifications.php" class="bell-btn">
          <?php if ($unread_count > 0): ?><span class="bell-dot"></span><?php endif; ?>
          <span class="icon"><svg><use href="#i-bell"/></svg></span>
        </a>
        <div class="avatar">
          <?php echo strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : '')); ?>
        </div>
      </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content">
      <!-- ACTION BANNER -->
      <div class="banner">
        <div>
          <h2>Ready to talk to someone?</h2>
          <p>Booking a session takes less than a minute — private, flexible, and always confidential.</p>
        </div>
        <a href="appointments.php?tab=book" class="btn btn-primary">
          <span class="icon"><svg><use href="#i-plus"/></svg></span>Book an Appointment
        </a>
      </div>

      <div class="grid cols-2">
        <!-- UPCOMING APPOINTMENT CARD -->
        <div class="card">
          <div class="card-head">
            <h3>Your Upcoming Appointment</h3>
            <a href="appointments.php" class="link">View all</a>
          </div>

          <?php if ($upcoming): ?>
            <div class="row-item">
              <div class="time-block">
                <div class="t"><?php echo date('g:i A', strtotime($upcoming['start_time'])); ?></div>
                <div class="d"><?php echo date('M j', strtotime($upcoming['appointment_date'])); ?></div>
              </div>
              <div class="info">
                <div class="title"><?php echo htmlspecialchars($upcoming['counselor_name']); ?></div>
                <div class="sub">Guidance Counselor · <?php echo htmlspecialchars($upcoming['concern']); ?></div>
              </div>
              <span class="pill <?php echo $upcoming['status']; ?>">
                <?php echo ucfirst($upcoming['status']); ?>
              </span>
            </div>
            <div class="row-item" style="border-bottom:none; padding-top:12px;">
              <div class="actions" style="margin-left:0; gap:10px;">
                <a href="appointments.php" class="btn btn-outline btn-sm">
                  <span class="icon"><svg><use href="#i-refresh"/></svg></span>Manage Appointment
                </a>
                <a href="appointments.php?cancel=<?php echo $upcoming['id']; ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Are you sure you want to cancel this appointment?');">Cancel</a>
              </div>
            </div>
          <?php else: ?>
            <div class="empty-note">
              No upcoming appointments scheduled right now.
              <div style="margin-top:10px;">
                <a href="appointments.php?tab=book" class="btn btn-ghost btn-sm">Book a session</a>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- QUOTE CARD & QUICK STATS -->
        <div class="quote-card">
          <p class="q">"It's okay to ask for help. Reaching out is a sign of strength, not weakness."</p>
          <p class="a">Today's reminder from the Guidance Office</p>
          <div style="margin-top:18px; display:flex; gap:24px;">
            <div>
              <div class="stat">
                <div class="num" style="font-size:22px;"><?php echo $stats['completed']; ?></div>
                <div class="lbl">Sessions attended</div>
              </div>
            </div>
            <div>
              <div class="stat">
                <div class="num" style="font-size:22px;"><?php echo max(1, min($stats['completed'], 3)); ?></div>
                <div class="lbl">Month streak</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RECENT NOTIFICATIONS -->
      <div class="card" style="margin-top:16px;">
        <div class="card-head">
          <h3>Recent Notifications</h3>
          <a href="notifications.php" class="link">View all</a>
        </div>

        <?php if (empty($recent_notifications)): ?>
          <div class="empty-note">No notifications to display.</div>
        <?php else: ?>
          <?php foreach ($recent_notifications as $notif): 
            $icon_class = 'violet';
            $icon_name = '#i-bell';
            if ($notif['type'] === 'approved') { $icon_class = 'green'; $icon_name = '#i-check'; }
            elseif ($notif['type'] === 'declined') { $icon_class = 'red'; $icon_name = '#i-x'; }
            elseif ($notif['type'] === 'rescheduled') { $icon_class = 'amber'; $icon_name = '#i-refresh'; }
            elseif ($notif['type'] === 'reminder') { $icon_class = 'violet'; $icon_name = '#i-clock'; }
          ?>
            <div class="notif-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
              <div class="notif-icon <?php echo $icon_class; ?>">
                <svg width="18" height="18"><use href="<?php echo $icon_name; ?>"/></svg>
              </div>
              <div>
                <div class="n-title"><?php echo ucfirst($notif['type']); ?></div>
                <div class="n-sub"><?php echo htmlspecialchars($notif['message']); ?></div>
              </div>
              <div class="n-time"><?php echo formatDate($notif['created_at'], 'M j, g:i A'); ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

</body>
</html>
