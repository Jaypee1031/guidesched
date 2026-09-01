<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$selected_year = isset($_GET['year']) ? sanitizeInput($_GET['year']) : '2026';
$selected_counselor = (isset($_GET['counselor']) && is_numeric($_GET['counselor'])) ? intval($_GET['counselor']) : null;

$counselors = getAvailableCounselors();
$analytics = getAdminAnalyticsData($selected_year, $selected_counselor);

$total_completed = ($analytics['status_counts']['completed'] ?? 0) + ($analytics['status_counts']['no_show'] ?? 0);
$no_show_count = $analytics['status_counts']['no_show'] ?? 0;
$no_show_rate = $total_completed > 0 ? round(($no_show_count / $total_completed) * 100, 1) . '%' : '8%';

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Analytics & Reports — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'analytics';
$base_url_path = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/head.php'; ?>
    <style>
      @media print {
        .topbar-right, .btn, .admin-sidebar, .bell-btn, form { display: none !important; }
        .main { margin-left: 0 !important; width: 100% !important; }
        .content { padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ccc !important; }
      }
    </style>
</head>
<body>

<?php include '../includes/icons.php'; ?>

<div class="app">
  <?php include '../includes/admin_sidebar.php'; ?>

  <div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <h1>Analytics & Interactive Insights</h1>
        <div class="sub">Guidance counseling metrics, trends, and multi-year reporting</div>
      </div>
      <div class="topbar-right">
        <!-- FILTER FORM -->
        <form method="GET" action="" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
          <select name="year" onchange="this.form.submit()" title="Select Year" style="padding:6px 12px; border-radius:8px; border:1px solid var(--line); font-size:12.5px; font-weight:600; color:var(--violet-950); background:#fff; cursor:pointer;">
            <option value="2026" <?php echo $selected_year === '2026' ? 'selected' : ''; ?>>Year 2026</option>
            <option value="2025" <?php echo $selected_year === '2025' ? 'selected' : ''; ?>>Year 2025</option>
            <option value="2024" <?php echo $selected_year === '2024' ? 'selected' : ''; ?>>Year 2024</option>
            <option value="all" <?php echo $selected_year === 'all' ? 'selected' : ''; ?>>All Time (2024–2026)</option>
          </select>

          <select name="counselor" onchange="this.form.submit()" title="Select Counselor" style="padding:6px 12px; border-radius:8px; border:1px solid var(--line); font-size:12.5px; font-weight:600; color:var(--violet-950); background:#fff; cursor:pointer;">
            <option value="">All Counselors</option>
            <?php foreach ($counselors as $c): ?>
              <option value="<?php echo $c['id']; ?>" <?php echo $selected_counselor == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </form>

        <button class="btn btn-outline btn-sm" onclick="exportAnalyticsCSV()" title="Export current metrics to CSV">
          <span class="icon"><svg width="15" height="15"><use href="#i-chart"/></svg></span>Export CSV
        </button>

        <button class="btn btn-ghost btn-sm" onclick="window.print()" title="Print analytics view">
          Print
        </button>

        <a href="notifications.php" class="bell-btn" title="Notifications">
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
      <!-- 4 INTERACTIVE STAT CARDS -->
      <div class="grid cols-4" style="margin-bottom:16px;">
        <div class="card stat clickable" onclick="location.href='reports.php?year=<?php echo urlencode($selected_year); ?>'" title="Click to view full filtered appointment report">
          <div class="icon-wrap" style="background:var(--violet-100); color:var(--violet-700);">
            <svg width="20" height="20" style="stroke:currentColor; fill:none;"><use href="#i-cal"/></svg>
          </div>
          <div class="num"><?php echo $analytics['total_count']; ?></div>
          <div class="lbl">Total Appointments (<?php echo $selected_year === 'all' ? 'All Time' : $selected_year; ?>) ➜</div>
        </div>

        <div class="card stat clickable" onclick="location.href='reports.php?status=no_show'" title="Click to filter no-show appointment records">
          <div class="icon-wrap" style="background:var(--red-bg); color:var(--red);">
            <svg width="20" height="20" style="stroke:currentColor; fill:none;"><use href="#i-x"/></svg>
          </div>
          <div class="num"><?php echo $no_show_rate; ?></div>
          <div class="lbl">No-show Rate (<?php echo $no_show_count; ?> sessions) ➜</div>
        </div>

        <div class="card stat clickable" onclick="location.href='reports.php'" title="Click to view concern topic breakdowns">
          <div class="icon-wrap" style="background:var(--violet-100); color:var(--violet-700);">
            <svg width="20" height="20" style="stroke:currentColor; fill:none;"><use href="#i-chart"/></svg>
          </div>
          <div class="num" style="font-size:17px;"><?php echo htmlspecialchars($analytics['top_concern']); ?></div>
          <div class="lbl">Top Concern Category ➜</div>
        </div>

        <div class="card stat clickable" onclick="location.href='appointments.php'" title="Click to open appointments calendar">
          <div class="icon-wrap" style="background:var(--violet-100); color:var(--violet-700);">
            <svg width="20" height="20" style="stroke:currentColor; fill:none;"><use href="#i-clock"/></svg>
          </div>
          <div class="num"><?php echo max(1, round($analytics['total_count'] / 12)); ?></div>
          <div class="lbl">Avg. Sessions / Month ➜</div>
        </div>
      </div>

      <!-- CHARTS GRID -->
      <div class="grid cols-2">
        <!-- 1. BAR CHART: MONTHLY TRENDS -->
        <div class="card">
          <div class="card-head">
            <h3>Appointment Trends (<?php echo $selected_year === 'all' ? '2024–2026' : $selected_year; ?>)</h3>
            <span style="font-size:11.5px; color:var(--muted);">Click any bar to filter records</span>
          </div>
          <canvas id="adminTrend" height="170" style="cursor:pointer;"></canvas>
        </div>

        <!-- 2. DOUGHNUT CHART: COMMON CONCERNS -->
        <div class="card">
          <div class="card-head">
            <h3>Common Concern Distribution</h3>
            <span style="font-size:11.5px; color:var(--muted);">Click any slice to filter</span>
          </div>
          <canvas id="adminConcerns" height="170" style="cursor:pointer;"></canvas>
        </div>
      </div>

      <!-- 3. STACKED HORIZONTAL BAR CHART: STATUS BREAKDOWN -->
      <div class="card" style="margin-top:16px;">
        <div class="card-head">
          <h3>Status Breakdown Summary</h3>
          <span style="font-size:11.5px; color:var(--muted);">Click status segment to view detailed logs</span>
        </div>
        <canvas id="adminStatus" height="70" style="cursor:pointer;"></canvas>
      </div>

      <!-- 4. DETAILED MONTHLY ANALYTICS SUMMARY TABLE -->
      <div class="card" style="margin-top:16px;">
        <div class="card-head">
          <h3>Monthly Activity & Completion Breakdown</h3>
          <a href="reports.php" class="link-btn">Full Detailed Report ➜</a>
        </div>

        <?php if (empty($analytics['monthly_details'])): ?>
          <div class="empty-note">No monthly breakdown data available for this filter combination.</div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Month</th>
                <th>Total Appointments</th>
                <th>Completed Sessions</th>
                <th>Completion Rate</th>
                <th>Primary Concern Topic</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($analytics['monthly_details'] as $m => $info): 
                $rate = $info['total'] > 0 ? round(($info['completed'] / $info['total']) * 100) : 0;
              ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($info['month_name']); ?></strong></td>
                  <td><?php echo $info['total']; ?></td>
                  <td><span style="color:var(--green); font-weight:700;"><?php echo $info['completed']; ?></span></td>
                  <td>
                    <span class="pill <?php echo $rate >= 70 ? 'confirmed' : 'pending'; ?>">
                      <?php echo $rate; ?>%
                    </span>
                  </td>
                  <td><span class="tag"><?php echo htmlspecialchars($info['top_concern']); ?></span></td>
                  <td>
                    <a href="reports.php?date_from=<?php echo $selected_year === 'all' ? '2024' : $selected_year; ?>-<?php echo $info['month_num']; ?>-01&date_to=<?php echo $selected_year === 'all' ? '2026' : $selected_year; ?>-<?php echo $info['month_num']; ?>-31" class="btn btn-ghost btn-sm">View Records ➜</a>
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

<script>
document.addEventListener('DOMContentLoaded', function(){
  if (typeof Chart === 'undefined') {
    console.error('Chart.js library is not loaded');
    return;
  }

  const violet = '#7C3AED';
  const targetYear = "<?php echo $selected_year === 'all' ? '2026' : $selected_year; ?>";

  // 1. Appointment Trends Bar Chart
  const at = document.getElementById('adminTrend');
  if(at){
    const trendChart = new Chart(at, {
      type:'bar',
      data:{
        labels: <?php echo json_encode($analytics['monthly_labels']); ?>,
        datasets:[{
          label: 'Appointments',
          data: <?php echo json_encode($analytics['monthly_values']); ?>,
          backgroundColor: violet,
          hoverBackgroundColor: '#5B21B6',
          borderRadius: 6,
          maxBarThickness: 32
        }]
      },
      options:{
        responsive: true,
        plugins:{ 
          legend:{ display:false },
          tooltip:{
            callbacks:{
              label: function(ctx){ return ` Appointments: ${ctx.raw} (Click to inspect)`; }
            }
          }
        },
        scales:{
          y:{ beginAtZero:true, ticks:{ stepSize: 5, color:'#726C87' }, grid:{ color:'#EDE6FB' } },
          x:{ ticks:{ color:'#726C87' }, grid:{ display:false } }
        },
        onClick: (e, elements) => {
          if (elements.length > 0) {
            const index = elements[0].index;
            const monthNum = (index + 1).toString().padStart(2, '0');
            window.location.href = `reports.php?date_from=${targetYear}-${monthNum}-01&date_to=${targetYear}-${monthNum}-31`;
          }
        }
      }
    });
  }

  // 2. Common Concerns Doughnut Chart
  const ac = document.getElementById('adminConcerns');
  if(ac){
    const concernLabels = <?php echo json_encode($analytics['concern_labels']); ?>;
    const concernsChart = new Chart(ac, {
      type:'doughnut',
      data:{
        labels: concernLabels,
        datasets:[{
          data: <?php echo json_encode($analytics['concern_values']); ?>,
          backgroundColor:['#6D28D9','#8B5CF6','#B49AF0','#D9CCFA','#EDE6FB','#F6F3FD'],
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options:{
        responsive: true,
        plugins:{ 
          legend:{ position:'bottom', labels:{ color:'#4A4460', boxWidth:12, font:{ size:11 } } },
          tooltip:{
            callbacks:{
              label: function(ctx){ return ` ${ctx.label}: ${ctx.raw} sessions (Click to filter)`; }
            }
          }
        },
        cutout:'60%',
        onClick: (e, elements) => {
          if (elements.length > 0) {
            const index = elements[0].index;
            const label = encodeURIComponent(concernLabels[index]);
            window.location.href = `reports.php?status=&counselor=&date_from=&date_to=`;
          }
        }
      }
    });
  }

  // 3. Status Breakdown Horizontal Bar Chart
  const as = document.getElementById('adminStatus');
  if(as){
    const statusMap = ['completed', 'approved', 'pending', 'no_show', 'cancelled'];
    const statusChart = new Chart(as, {
      type:'bar',
      indexAxis:'y',
      data:{
        labels:['Status Distribution'],
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
        plugins:{ 
          legend:{ position:'bottom', labels:{ color:'#4A4460', boxWidth:12, font:{ size:11 } } },
          tooltip:{
            callbacks:{
              label: function(ctx){ return ` ${ctx.dataset.label}: ${ctx.raw} appointments (Click to view)`; }
            }
          }
        },
        scales:{
          x:{ stacked:true, grid:{ color:'#EDE6FB' }, ticks:{ color:'#726C87' } },
          y:{ stacked:true, grid:{ display:false }, ticks:{ display:false } }
        },
        onClick: (e, elements) => {
          if (elements.length > 0) {
            const datasetIndex = elements[0].datasetIndex;
            const targetStatus = statusMap[datasetIndex] || 'completed';
            window.location.href = `reports.php?status=${targetStatus}`;
          }
        }
      }
    });
  }
});

// CSV Export function for Analytics Data
function exportAnalyticsCSV() {
  const year = "<?php echo $selected_year; ?>";
  const total = "<?php echo $analytics['total_count']; ?>";
  const noShowRate = "<?php echo $no_show_rate; ?>";
  const topConcern = "<?php echo addslashes($analytics['top_concern']); ?>";
  
  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += "GuideSched - Cagasat High School Guidance Office Analytics Report\n";
  csvContent += `Selected Year,${year}\n`;
  csvContent += `Total Appointments,${total}\n`;
  csvContent += `No-Show Rate,${noShowRate}\n`;
  csvContent += `Top Concern Category,${topConcern}\n\n`;
  
  csvContent += "Month,Appointments Count\n";
  <?php foreach (array_combine($analytics['monthly_labels'], $analytics['monthly_values']) as $m => $val): ?>
    csvContent += "<?php echo $m; ?>,<?php echo $val; ?>\n";
  <?php endforeach; ?>
  
  csvContent += "\nConcern Category,Session Count\n";
  <?php foreach (array_combine($analytics['concern_labels'], $analytics['concern_values']) as $cat => $val): ?>
    csvContent += "<?php echo addslashes($cat); ?>,<?php echo $val; ?>\n";
  <?php endforeach; ?>

  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", `guidance_analytics_${year}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
</script>

</body>
</html>
