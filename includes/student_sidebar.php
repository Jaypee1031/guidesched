<?php
if (!isset($active_page)) {
    $active_page = 'home';
}
$student_name = $_SESSION['name'] ?? 'Student';
$initials = '';
$name_parts = explode(' ', trim($student_name));
if (count($name_parts) >= 2) {
    $initials = strtoupper(substr($name_parts[0], 0, 1) . substr(end($name_parts), 0, 1));
} else {
    $initials = strtoupper(substr($student_name, 0, 2));
}

// Get course/year info if user profile is available
$sub_info = 'Student Portal';
if (isset($user) && !empty($user['course'])) {
    $sub_info = htmlspecialchars($user['course']) . ($user['year_level'] ? ' · Yr ' . $user['year_level'] : '');
}
?>
<div class="sidebar">
  <div class="brand">
    <div class="brand-mark">GS</div>
    <div class="brand-text">
      <div class="name">GuideSched</div>
      <div class="portal">STUDENT PORTAL</div>
    </div>
  </div>
  
  <a class="navlink <?php echo $active_page === 'home' ? 'active' : ''; ?>" href="dashboard.php">
    <span class="icon"><svg><use href="#i-home"/></svg></span>Home
  </a>
  <a class="navlink <?php echo $active_page === 'appointments' ? 'active' : ''; ?>" href="appointments.php">
    <span class="icon"><svg><use href="#i-cal"/></svg></span>Appointments
  </a>
  <a class="navlink <?php echo $active_page === 'notifications' ? 'active' : ''; ?>" href="notifications.php">
    <span class="icon"><svg><use href="#i-bell"/></svg></span>Notifications
  </a>
  <a class="navlink <?php echo $active_page === 'analytics' ? 'active' : ''; ?>" href="analytics.php">
    <span class="icon"><svg><use href="#i-chart"/></svg></span>My Insights
  </a>

  <div class="sidebar-foot">
    <a class="mini-profile <?php echo $active_page === 'profile' ? 'active' : ''; ?>" href="profile.php">
      <div class="avatar"><?php echo $initials; ?></div>
      <div class="who">
        <div class="n"><?php echo htmlspecialchars($student_name); ?></div>
        <div class="r"><?php echo $sub_info; ?></div>
      </div>
    </a>
    <a class="navlink logout-link" href="../logout.php">
      <span class="icon"><svg><use href="#i-logout"/></svg></span>Log Out
    </a>
  </div>
</div>
