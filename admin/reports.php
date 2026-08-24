<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireRole('admin');

$user = getUserProfile($_SESSION['user_id']);
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$counselor_filter = isset($_GET['counselor']) && is_numeric($_GET['counselor']) ? intval($_GET['counselor']) : null;
$date_from = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';

$appointments = getAllAppointments($status_filter, $counselor_filter, $date_from, $date_to);
$counselors = getAvailableCounselors();
$stats = getAdminStatistics();

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$page_title = 'Reports — Admin Portal — GuideSched';
$active_page = 'reports';
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
        <h1>Reports</h1>
        <div class="sub">Generate and export guidance counseling reports</div>
      </div>
      <div class="topbar-right">
        <button class="btn btn-primary" onclick="exportToExcel()">
          <span class="icon"><svg><use href="#i-chart"/></svg></span>Export CSV
        </button>
        <button class="btn btn-outline" onclick="window.print()">Print</button>
        <a href="notifications.php" class="bell-btn">
          <?php if ($unread_count > 0): ?><span class="bell-dot"></span><?php endif; ?>
          <span class="icon"><svg><use href="#i-bell"/></svg></span>
        </a>
        <div class="avatar" style="background:var(--violet-700);">
          <?php echo strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : '')); ?>
        </div>
      </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content">
      <!-- FILTER FORM CARD -->
      <div class="card" style="margin-bottom:16px;">
        <h3 style="margin-bottom:14px;">Report Parameters</h3>
        <form method="GET" action="">
          <div class="form-grid">
            <div class="field">
              <label>Status</label>
              <select name="status">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
              </select>
            </div>
            <div class="field">
              <label>Counselor</label>
              <select name="counselor">
                <option value="">All Counselors</option>
                <?php foreach ($counselors as $c): ?>
                  <option value="<?php echo $c['id']; ?>" <?php echo $counselor_filter == $c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>From Date</label>
              <input type="date" name="date_from" value="<?php echo $date_from; ?>">
            </div>
            <div class="field">
              <label>To Date</label>
              <input type="date" name="date_to" value="<?php echo $date_to; ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:6px;">Filter Results</button>
        </form>
      </div>

      <!-- REPORT SUMMARY STATS -->
      <div class="grid cols-4" style="margin-bottom:16px;">
        <div class="card stat">
          <div class="num"><?php echo count($appointments); ?></div>
          <div class="lbl">Filtered Appointments</div>
        </div>
        <div class="card stat">
          <div class="num" style="color:var(--green);"><?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'completed')); ?></div>
          <div class="lbl">Completed</div>
        </div>
        <div class="card stat">
          <div class="num" style="color:var(--amber);"><?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'pending')); ?></div>
          <div class="lbl">Pending</div>
        </div>
        <div class="card stat">
          <div class="num" style="color:var(--red);"><?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'cancelled')); ?></div>
          <div class="lbl">Cancelled</div>
        </div>
      </div>

      <!-- REPORT TABLE -->
      <div class="card">
        <div class="card-head">
          <h3>Appointment Summary Report</h3>
        </div>

        <?php if (empty($appointments)): ?>
          <div class="empty-note">No appointments match the selected parameters.</div>
        <?php else: ?>
          <div id="reportTableContainer">
            <table id="reportTable">
              <thead>
                <tr>
                  <th>Date & Time</th>
                  <th>Student</th>
                  <th>Counselor</th>
                  <th>Concern Topic</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($appointments as $apt): ?>
                  <tr>
                    <td><?php echo date('M j, Y', strtotime($apt['appointment_date'])) . ' (' . date('g:i A', strtotime($apt['start_time'])) . ')'; ?></td>
                    <td><?php echo htmlspecialchars($apt['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($apt['counselor_name']); ?></td>
                    <td><?php echo htmlspecialchars($apt['concern']); ?></td>
                    <td><span class="pill <?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script>
function exportToExcel() {
    let csv = [];
    const rows = document.querySelectorAll("#reportTable tr");
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length; j++) {
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        }
        csv.push(row.join(","));
    }
    const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    const downloadLink = document.createElement("a");
    downloadLink.download = "guidesched_report.csv";
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>

</body>
</html>
