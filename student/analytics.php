<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

$user = getUserProfile($_SESSION['user_id']);
$analytics = getStudentAnalyticsData($_SESSION['user_id']);
$unread_count = count(getStudentNotifications($_SESSION['user_id'], true));

$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'My Insights — GuideSched — Cagasat High School';
$active_page = 'analytics';
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
        <h1>My Insights</h1>
        <div class="sub">A private look at your counseling journey</div>
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
      <div class="grid cols-3" style="margin-bottom:16px;">
        <div class="card stat clickable" onclick="location.href='appointments.php?tab=past'" title="Click to view past sessions">
          <div class="icon-wrap"><svg><use href="#i-heart"/></svg></div>
          <div class="num"><?php echo $analytics['attended_count']; ?></div>
          <div class="lbl">Sessions attended ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='appointments.php?tab=book'" title="Click to book new session">
          <div class="icon-wrap"><svg><use href="#i-chart"/></svg></div>
          <div class="num" style="font-size:20px;"><?php echo htmlspecialchars($analytics['top_concern']); ?></div>
          <div class="lbl">Most discussed topic ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='appointments.php'" title="Click to view all appointments">
          <div class="icon-wrap"><svg><use href="#i-shield"/></svg></div>
          <div class="num" style="font-size:20px;"><?php echo $analytics['streak']; ?></div>
          <div class="lbl">Consistency streak ➜</div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <h3>Sessions per Month</h3>
        </div>
        <canvas id="studentChart" height="90"></canvas>
      </div>

      <p style="font-size:11.5px;color:var(--faint);margin-top:12px;">Only you can see this page. Your counselor sees session notes separately, in line with confidentiality guidelines.</p>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const sc = document.getElementById('studentChart');
  if(sc){
    new Chart(sc, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($analytics['monthly_labels']); ?>,
        datasets: [{
          data: <?php echo json_encode($analytics['monthly_values']); ?>,
          backgroundColor: '#7C3AED',
          borderRadius: 6,
          maxBarThickness: 36
        }]
      },
      options: {
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1, color: '#726C87' }, grid: { color: '#EDE6FB' } },
          x: { ticks: { color: '#726C87' }, grid: { display: false } }
        }
      }
    });
  }
});
</script>

</body>
</html>
