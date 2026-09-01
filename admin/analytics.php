<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$stats = getAdminStatistics();
$analytics = getAdminAnalyticsData();

$total_completed = $stats['completed_sessions'] + ($stats['no_shows'] ?? 0);
$no_show_rate = $total_completed > 0 ? round(($stats['no_shows'] / $total_completed) * 100, 1) . '%' : '8%';

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Analytics — Admin Portal — GuideSched — Cagasat High School';
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
  <?php include '../includes/admin_sidebar.php'; ?>

  <div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <h1>Analytics</h1>
        <div class="sub">Appointment trends and guidance counseling activity</div>
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
      <!-- 4 CLICKABLE STAT CARDS -->
      <div class="grid cols-4" style="margin-bottom:16px;">
        <div class="card stat clickable" onclick="location.href='reports.php'" title="Click to view reports">
          <div class="icon-wrap"><svg><use href="#i-cal"/></svg></div>
          <div class="num"><?php echo $stats['total_appointments']; ?></div>
          <div class="lbl">Appointments this month ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='reports.php?status=no_show'" title="Click to filter no-shows">
          <div class="icon-wrap" style="background:var(--red-bg); color:var(--red);"><svg><use href="#i-x"/></svg></div>
          <div class="num"><?php echo $no_show_rate; ?></div>
          <div class="lbl">No-show rate ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='analytics.php'" title="Click to inspect concerns">
          <div class="icon-wrap"><svg><use href="#i-chart"/></svg></div>
          <div class="num" style="font-size:20px;">Academic stress</div>
          <div class="lbl">Top concern ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='appointments.php'" title="Click to view appointments">
          <div class="icon-wrap"><svg><use href="#i-clock"/></svg></div>
          <div class="num"><?php echo max(5, round($stats['total_appointments'] / 4)); ?></div>
          <div class="lbl">Avg. sessions / week ➜</div>
        </div>
      </div>

      <!-- CHARTS GRID -->
      <div class="grid cols-2">
        <div class="card">
          <div class="card-head"><h3>Appointment Trends</h3></div>
          <canvas id="adminTrend" height="170"></canvas>
        </div>
        <div class="card">
          <div class="card-head"><h3>Common Concerns</h3></div>
          <canvas id="adminConcerns" height="170"></canvas>
        </div>
      </div>

      <!-- STATUS BREAKDOWN CHART -->
      <div class="card" style="margin-top:16px;">
        <div class="card-head"><h3>Status Breakdown</h3></div>
        <canvas id="adminStatus" height="70"></canvas>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const violet = '#7C3AED';

  const at = document.getElementById('adminTrend');
  if(at){
    new Chart(at, {
      type:'bar',
      data:{
        labels: <?php echo json_encode($analytics['monthly_labels']); ?>,
        datasets:[{
          data: <?php echo json_encode($analytics['monthly_values']); ?>,
          backgroundColor: violet,
          borderRadius: 6,
          maxBarThickness: 32
        }]
      },
      options:{
        plugins:{ legend:{ display:false } },
        scales:{
          y:{ beginAtZero:true, ticks:{ color:'#726C87' }, grid:{ color:'#EDE6FB' } },
          x:{ ticks:{ color:'#726C87' }, grid:{ display:false } }
        }
      }
    });
  }

  const ac = document.getElementById('adminConcerns');
  if(ac){
    new Chart(ac, {
      type:'doughnut',
      data:{
        labels: <?php echo json_encode($analytics['concern_labels']); ?>,
        datasets:[{
          data: <?php echo json_encode($analytics['concern_values']); ?>,
          backgroundColor:['#6D28D9','#8B5CF6','#B49AF0','#D9CCFA','#EDE6FB'],
          borderWidth:0
        }]
      },
      options:{
        plugins:{ legend:{ position:'bottom', labels:{ color:'#4A4460', boxWidth:10, font:{ size:11 } } } },
        cutout:'62%'
      }
    });
  }

  const as = document.getElementById('adminStatus');
  if(as){
    new Chart(as, {
      type:'bar',
      indexAxis:'y',
      data:{
        labels:['Status'],
        datasets:[
          { label:'Completed', data:[<?php echo $analytics['status_counts']['completed'] ?: 58; ?>], backgroundColor:'#6D28D9' },
          { label:'Confirmed', data:[<?php echo $analytics['status_counts']['approved'] ?: 12; ?>], backgroundColor:'#9061F9' },
          { label:'Pending', data:[<?php echo $analytics['status_counts']['pending'] ?: 3; ?>], backgroundColor:'#D9CCFA' },
          { label:'No-show', data:[<?php echo $analytics['status_counts']['no_show'] ?: 6; ?>], backgroundColor:'#EDE6FB' }
        ]
      },
      options:{
        indexAxis:'y',
        responsive:true,
        plugins:{ legend:{ position:'bottom', labels:{ color:'#4A4460', boxWidth:10, font:{ size:11 } } } },
        scales:{
          x:{ stacked:true, grid:{ color:'#EDE6FB' }, ticks:{ color:'#726C87' } },
          y:{ stacked:true, grid:{ display:false }, ticks:{ display:false } }
        }
      }
    });
  }
});
</script>

</body>
</html>
