<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);

if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    markNotificationAsRead(intval($_GET['mark_read']), $_SESSION['user_id']);
    redirect('admin/notifications.php');
}

if (isset($_GET['mark_all_read'])) {
    $unread = getAdminNotifications($_SESSION['user_id'], true);
    foreach ($unread as $n) {
        markNotificationAsRead($n['id'], $_SESSION['user_id']);
    }
    redirect('admin/notifications.php');
}

$all_notifications = getAdminNotifications($_SESSION['user_id']);
$unread_notifications = getAdminNotifications($_SESSION['user_id'], true);
$unread_count = count($unread_notifications);

$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Notifications — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'notifications';
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
        <h1>Notifications</h1>
        <div class="sub">Booking activity and system alerts</div>
      </div>
      <div class="topbar-right">
        <?php if ($unread_count > 0): ?>
          <a href="notifications.php?mark_all_read=1" class="btn btn-outline btn-sm">Mark all read</a>
        <?php endif; ?>
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
      <div class="card">
        <?php if (empty($all_notifications)): ?>
          <div class="empty-note">No admin notifications found.</div>
        <?php else: ?>
          <?php foreach ($all_notifications as $notif): 
            $icon_class = 'violet';
            $icon_name = '#i-bell';
            if ($notif['type'] === 'approved') { $icon_class = 'green'; $icon_name = '#i-check'; }
            elseif ($notif['type'] === 'declined') { $icon_class = 'red'; $icon_name = '#i-x'; }
            elseif ($notif['type'] === 'rescheduled') { $icon_class = 'amber'; $icon_name = '#i-refresh'; }
            elseif ($notif['type'] === 'reminder') { $icon_class = 'violet'; $icon_name = '#i-clock'; }
          ?>
            <div class="notif-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>">
              <div class="unread-dot" style="<?php echo $notif['is_read'] ? 'visibility:hidden' : ''; ?>"></div>
              <div class="notif-icon <?php echo $icon_class; ?>">
                <svg width="18" height="18"><use href="<?php echo $icon_name; ?>"/></svg>
              </div>
              <div style="flex:1;">
                <div class="n-title"><?php echo ucfirst($notif['type']); ?></div>
                <div class="n-sub"><?php echo htmlspecialchars($notif['message']); ?></div>
                <?php if (!$notif['is_read']): ?>
                  <div style="margin-top:6px;">
                    <a href="notifications.php?mark_read=<?php echo $notif['id']; ?>" class="link-btn" style="font-size:11.5px;">Mark as read</a>
                  </div>
                <?php endif; ?>
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
