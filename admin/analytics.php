<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$selected_year = isset($_GET['year']) ? sanitizeInput($_GET['year']) : '2026';

$stats = getAdminStatistics();
$analytics = getAdminAnalyticsData($selected_year);

$total_completed = ($analytics['status_counts']['completed'] ?? 0) + ($analytics['status_counts']['no_show'] ?? 0);
$no_show_rate = $total_completed > 0 ? round((($analytics['status_counts']['no_show'] ?? 0) / $total_completed) * 100, 1) . '%' : '8%';

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
        <h1>Analytics & Insights</h1>
        <div class="sub">Appointment trends, concern breakdown, and multi-year data</div>
      </div>
      <div class="topbar-right">
        <!-- YEAR FILTER SELECTOR -->
        <form method="GET" action="" style="display:flex; align-items:center; gap:8px;">
          <label style="font-size:12px; font-weight:700; color:var(--muted);">Filter Year:</label>
          <select name="year" onchange="this.form.submit()" style="padding:6px 12px; border-radius:8px; border:1px solid var(--line); font-size:13px; font-weight:600; color:var(--violet-950); background:#fff; cursor:pointer;">
            <option value="2026" <?php echo $selected_year === '2026' ? 'selected' : ''; ?>>Year 2026</option>
            <option value="2025" <?php echo $selected_year === '2025' ? 'selected' : ''; ?>>Year 2025</option>
            <option value="2024" <?php echo $selected_year === '2024' ? 'selected' : ''; ?>>Year 2024</option>
            <option value="all" <?php echo $selected_year === 'all' ? 'selected' : ''; ?>>All Time (2024–2026)</option>
          </select>
        </form>

        <a href="notifications.php" class="bell-btn">
          <?php if ($unread_count > 0): ?><span class="bell-dot"></span><?php endif; ?>
          <span class="icon"><svg width="18" height="18"><use href="#i-bell"/></svg></span>
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
          <div class="icon-wrap" style="background:var(--violet-100); color:var(--violet-700);">
            <svg width="20" height="20" style="stroke:currentColor; fill:none;"><use href="#i-cal"/></svg>
          </div>
          <div class="num"><?php echo $analytics['total_count']; ?></div>
          <div class="lbl">Total Appointments (<?php echo $selected_year === 'all' ? 'All Time' : $selected_year; ?>) ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='reports.php?status=no_show'" title="Click to filter no-shows">
          <div class="icon-wrap" style="background:var(--red-bg); color:var(--red);">
            <svg width="20" height="20" style="stroke:currentColor; fill:none;"><use href="#i-x"/></svg>
          </div>
          <div class="num"><?php echo $no_show_rate; ?></div>
          <div class="lbl">No-show Rate ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='analytics.php'" title="Click to inspect top concern">
          <div class="icon-wrap" style="background:var(--violet-100); color:var(--violet-700);">
            <svg width="20" height="20" style="stroke:currentColor; fill:none;"><use href="#i-chart"/></svg>
          </div>
          <div class="num" style="font-size:18px;"><?php echo htmlspecialchars($analytics['top_concern']); ?></div>
          <div class="lbl">Top Concern Category ➜</div>
        </div>
        <div class="card stat clickable" onclick="location.href='appointments.php'" title="Click to view appointments">
          <div class="icon-wrap" style="background:var(--violet-100); color:var(--violet-700);">
            <svg width="20" height="20" style="stroke:currentColor; fill:none;"><use href="#i-clock"/></svg>
          </div>
          <div class="num"><?php echo max(4, round($analytics['total_count'] / 12)); ?></div>
          <div class="lbl">Avg. Sessions / Month ➜</div>
        </div>
      </div>

      <!-- CHARTS GRID -->
      <div class="grid cols-2">
        <div class="card">
          <div class="card-head">
            <h3>Appointment Trends (<?php echo $selected_year === 'all' ? '2024–2026' : $selected_year; ?>)</h3>
          </div>
          <canvas id="adminTrend" height="170"></canvas>
        </div>
        <div class="card">
          <div class="card-head">
            <h3>Common Concern Distribution</h3>
          </div>
          <canvas id="adminConcerns" height="170"></canvas>
        </div>
      </div>

      <!-- STATUS BREAKDOWN CHART -->
      <div class="card" style="margin-top:16px;">
        <div class="card-head">
          <h3>Status Breakdown Summary</h3>
        </div>
        <canvas id="adminStatus" height="70"></canvas>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
  if (typeof Chart === 'undefined') {
    console.error('Chart.js library is not loaded');
    return;
  }

  const violet = '#7C3AED';

  // 1. Appointment Trends Bar Chart
  const at = document.getElementById('adminTrend');
  if(at){
    new Chart(at, {
      type:'bar',
      data:{
        labels: <?php echo json_encode($analytics['monthly_labels']); ?>,
        datasets:[{
          label: 'Appointments',
          data: <?php echo json_encode($analytics['monthly_values']); ?>,
          backgroundColor: violet,
          borderRadius: 6,
          maxBarThickness: 32
        }]
      },
      options:{
        responsive: true,
        plugins:{ legend:{ display:false } },
        scales:{
          y:{ beginAtZero:true, ticks:{ stepSize: 5, color:'#726C87' }, grid:{ color:'#EDE6FB' } },
          x:{ ticks:{ color:'#726C87' }, grid:{ display:false } }
        }
      }
    });
  }

  // 2. Common Concerns Doughnut Chart
  const ac = document.getElementById('adminConcerns');
  if(ac){
    new Chart(ac, {
      type:'doughnut',
      data:{
        labels: <?php echo json_encode($analytics['concern_labels']); ?>,
        datasets:[{
          data: <?php echo json_encode($analytics['concern_values']); ?>,
          backgroundColor:['#6D28D9','#8B5CF6','#B49AF0','#D9CCFA','#EDE6FB','#F6F3FD'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options:{
        responsive: true,
        plugins:{ legend:{ position:'bottom', labels:{ color:'#4A4460', boxWidth:12, font:{ size:11 } } } },
        cutout:'60%'
      }
    });
  }

  // 3. Status Breakdown Horizontal Bar Chart
  const as = document.getElementById('adminStatus');
  if(as){
    new Chart(as, {
      type:'bar',
      indexAxis:'y',
      data:{
        labels:['Appointments'],
        datasets:[
          { label:'Completed', data:[<?php echo $analytics['status_counts']['completed'] ?: 0; ?>], backgroundColor:'#6D28D9' },
          { label:'Approved', data:[<?php echo $analytics['status_counts']['approved'] ?: 0; ?>], backgroundColor:'#9061F9' },
          { label:'Pending', data:[<?php echo $analytics['status_counts']['pending'] ?: 0; ?>], backgroundColor:'#D9CCFA' },
          { label:'No-show', data:[<?php echo $analytics['status_counts']['no_show'] ?: 0; ?>], backgroundColor:'#F5C6CB' },
          { label:'Cancelled', data:[<?php echo $analytics['status_counts']['cancelled'] ?: 0; ?>], backgroundColor:'#EDE6FB' }
        ]
      },
      options:{
        indexAxis:'y',
        responsive:true,
        plugins:{ legend:{ position:'bottom', labels:{ color:'#4A4460', boxWidth:12, font:{ size:11 } } } },
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
