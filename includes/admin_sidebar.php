<?php
if (!isset($active_page)) {
    $active_page = 'dashboard';
}
$admin_name = $_SESSION['name'] ?? 'Counselor';
$user_role = $_SESSION['role'] ?? 'admin';
$initials = '';
$name_parts = explode(' ', trim($admin_name));
if (count($name_parts) >= 2) {
    $initials = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));
} else {
    $initials = strtoupper(substr($admin_name, 0, 2));
}

$role_title = ucfirst($user_role);
if (isset($user) && !empty($user['specialization'])) {
    $role_title = htmlspecialchars($user['specialization']);
}
?>
<div class="sidebar">
  <div class="brand">
    <div class="brand-mark">GS</div>
    <div class="brand-text">
      <div class="name">GuideSched</div>
      <div class="portal"><?php echo strtoupper($user_role); ?> PORTAL</div>
    </div>
  </div>

  <a class="navlink <?php echo $active_page === 'dashboard' ? 'active' : ''; ?>" href="dashboard.php">
    <span class="icon"><svg><use href="#i-home"/></svg></span>Dashboard
  </a>
  <a class="navlink <?php echo $active_page === 'appointments' ? 'active' : ''; ?>" href="appointments.php">
    <span class="icon"><svg><use href="#i-cal"/></svg></span>Appointments
  </a>
  <a class="navlink <?php echo $active_page === 'schedule' ? 'active' : ''; ?>" href="schedule.php">
    <span class="icon"><svg><use href="#i-clock"/></svg></span>Schedule Management
  </a>
  <a class="navlink <?php echo $active_page === 'notifications' ? 'active' : ''; ?>" href="notifications.php">
    <span class="icon"><svg><use href="#i-bell"/></svg></span>Notifications
  </a>
  <a class="navlink <?php echo $active_page === 'students' ? 'active' : ''; ?>" href="students.php">
    <span class="icon"><svg><use href="#i-user"/></svg></span>Students
  </a>
  <?php if ($user_role === 'admin'): ?>
  <a class="navlink <?php echo $active_page === 'counselors' ? 'active' : ''; ?>" href="counselors.php">
    <span class="icon"><svg><use href="#i-shield"/></svg></span>Counselors
  </a>
  <?php endif; ?>
  <a class="navlink <?php echo $active_page === 'analytics' ? 'active' : ''; ?>" href="analytics.php">
    <span class="icon"><svg><use href="#i-chart"/></svg></span>Analytics
  </a>
  <?php if ($user_role === 'admin'): ?>
  <a class="navlink <?php echo $active_page === 'reports' ? 'active' : ''; ?>" href="reports.php">
    <span class="icon"><svg><use href="#i-mail"/></svg></span>Reports
  </a>
  <?php endif; ?>
  <a class="navlink" href="javascript:void(0)" onclick="triggerPWAInstall()" style="color:var(--violet-600); font-weight:700;">
    <span class="icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg></span>Download App
  </a>

  <div class="sidebar-foot">
    <a class="mini-profile <?php echo $active_page === 'profile' ? 'active' : ''; ?>" href="profile.php">
      <div class="avatar" style="background:var(--violet-700);"><?php echo $initials; ?></div>
      <div class="who">
        <div class="n"><?php echo htmlspecialchars($admin_name); ?></div>
        <div class="r"><?php echo $role_title; ?></div>
      </div>
    </a>
    <a class="navlink logout-link" href="../logout.php">
      <span class="icon"><svg><use href="#i-logout"/></svg></span>Log Out
    </a>
  </div>
</div>
