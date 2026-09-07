<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$error = '';
$success = '';

// Handle appointment actions
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $action = sanitizeInput($_GET['action']);
    
    if ($action === 'approve') {
        $result = updateAppointmentStatus($appointment_id, 'approved', $_SESSION['user_id']);
        if ($result['success']) { $success = $result['message']; } else { $error = $result['message']; }
    } elseif ($action === 'decline') {
        $result = updateAppointmentStatus($appointment_id, 'declined', $_SESSION['user_id']);
        if ($result['success']) { $success = $result['message']; } else { $error = $result['message']; }
    } elseif ($action === 'complete') {
        $result = updateAppointmentStatus($appointment_id, 'completed', $_SESSION['user_id']);
        if ($result['success']) { $success = $result['message']; } else { $error = $result['message']; }
    } elseif ($action === 'noshow') {
        $result = updateAppointmentStatus($appointment_id, 'no_show', $_SESSION['user_id']);
        if ($result['success']) { $success = $result['message']; } else { $error = $result['message']; }
    }
}

// Handle notes update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notes'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $admin_notes = sanitizeInput($_POST['admin_notes']);
    $current_status = sanitizeInput($_POST['current_status']);
    
    $result = updateAppointmentStatus($appointment_id, $current_status, $_SESSION['user_id'], $admin_notes);
    if ($result['success']) { $success = $result['message']; } else { $error = $result['message']; }
}

$pending_appointments = getAllAppointments('pending');
$approved_appointments = getAllAppointments('approved');
$all_appointments = getAllAppointments();

$active_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'pending';

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Appointments — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'appointments';
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
        <h1>Appointments</h1>
        <div class="sub">Approve, reschedule, and manage counselor availability</div>
      </div>
      <div class="topbar-right">
        <a href="schedule.php" class="btn btn-primary">
          <span class="icon"><svg><use href="#i-plus"/></svg></span>Block Time Slot
        </a>
        <a href="notifications.php" class="bell-btn" title="Notifications">
          <?php if ($unread_count > 0): ?><span class="bell-dot"></span><?php endif; ?>
          <span class="icon"><svg width="18" height="18"><use href="#i-bell"/></svg></span>
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

      <!-- TABBAR -->
      <div class="tabbar">
        <button class="<?php echo $active_tab === 'pending' ? 'active' : ''; ?>" onclick="showTab(this, 'ad-pending')">Pending (<?php echo count($pending_appointments); ?>)</button>
        <button class="<?php echo $active_tab === 'approved' ? 'active' : ''; ?>" onclick="showTab(this, 'ad-approved')">Approved (<?php echo count($approved_appointments); ?>)</button>
        <button class="<?php echo $active_tab === 'slots' ? 'active' : ''; ?>" onclick="showTab(this, 'ad-slots')">Time Slots</button>
      </div>

      <!-- PENDING TAB -->
      <div id="ad-pending" class="tabpane" style="display: <?php echo $active_tab === 'pending' ? 'block' : 'none'; ?>;">
        <div class="card">
          <?php if (empty($pending_appointments)): ?>
            <div class="empty-note">No pending appointment requests.</div>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Requested Date & Time</th>
                  <th>Concern</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pending_appointments as $apt): ?>
                  <tr>
                    <td>
                      <div class="name-cell">
                        <div class="avatar"><?php echo strtoupper(substr($apt['student_name'], 0, 2)); ?></div>
                        <?php echo htmlspecialchars($apt['student_name']); ?>
                      </div>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($apt['appointment_date'])) . ' at ' . date('g:i A', strtotime($apt['start_time'])); ?></td>
                    <td><span class="tag"><?php echo htmlspecialchars(substr($apt['concern'], 0, 35)); ?></span></td>
                    <td><span class="pill pending">Pending</span></td>
                    <td>
                      <div class="actions" style="margin-left:0; gap:6px;">
                        <a href="appointments.php?action=approve&id=<?php echo $apt['id']; ?>" class="btn btn-approve btn-sm">Approve</a>
                        <a href="appointments.php?action=decline&id=<?php echo $apt['id']; ?>" class="btn btn-decline btn-sm" onclick="return confirm('Decline this request?');">Decline</a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- APPROVED TAB -->
      <div id="ad-approved" class="tabpane" style="display: <?php echo $active_tab === 'approved' ? 'block' : 'none'; ?>;">
        <div class="card">
          <?php if (empty($approved_appointments)): ?>
            <div class="empty-note">No approved appointments found.</div>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Student</th>
                  <th>Date & Time</th>
                  <th>Concern</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($approved_appointments as $apt): ?>
                  <tr>
                    <td>
                      <div class="name-cell">
                        <div class="avatar"><?php echo strtoupper(substr($apt['student_name'], 0, 2)); ?></div>
                        <?php echo htmlspecialchars($apt['student_name']); ?>
                      </div>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($apt['appointment_date'])) . ' at ' . date('g:i A', strtotime($apt['start_time'])); ?></td>
                    <td><span class="tag"><?php echo htmlspecialchars(substr($apt['concern'], 0, 35)); ?></span></td>
                    <td><span class="pill confirmed">Confirmed</span></td>
                    <td>
                      <div class="actions" style="margin-left:0; gap:6px;">
                        <a href="appointments.php?action=complete&id=<?php echo $apt['id']; ?>" class="btn btn-ghost btn-sm" style="color:var(--green);">Mark Complete</a>
                        <a href="appointments.php?action=noshow&id=<?php echo $apt['id']; ?>" class="btn btn-ghost btn-sm" style="color:var(--amber);" onclick="return confirm('Mark as No-show?');">No-show</a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

      <!-- TIME SLOTS TAB -->
      <div id="ad-slots" class="tabpane" style="display: <?php echo $active_tab === 'slots' ? 'block' : 'none'; ?>;">
        <div class="card">
          <div class="card-head">
            <div>
              <h3 style="margin:0;">Weekly Availability Grid</h3>
              <div style="font-size:12px; color:var(--muted); margin-top:2px;">Guidance Office Regular Hours · Weekdays Only (Mon – Fri)</div>
            </div>
            <a href="schedule.php" class="btn btn-ghost btn-sm">
              <span class="icon"><svg><use href="#i-edit"/></svg></span>Edit Availability
            </a>
          </div>
          <div style="overflow-x:auto;">
            <table class="grid-table" style="width:100%; border-collapse:separate; border-spacing:6px; margin-top:10px;">
              <thead>
                <tr>
                  <th style="width:75px; font-size:11px; font-weight:700; color:var(--faint); padding:6px 0; text-align:center;">Time</th>
                  <th style="font-size:11px; font-weight:700; color:var(--faint); padding:6px 0; text-align:center;">Mon</th>
                  <th style="font-size:11px; font-weight:700; color:var(--faint); padding:6px 0; text-align:center;">Tue</th>
                  <th style="font-size:11px; font-weight:700; color:var(--faint); padding:6px 0; text-align:center;">Wed</th>
                  <th style="font-size:11px; font-weight:700; color:var(--faint); padding:6px 0; text-align:center;">Thu</th>
                  <th style="font-size:11px; font-weight:700; color:var(--faint); padding:6px 0; text-align:center;">Fri</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-size:11px; color:var(--faint); font-weight:600; text-align:center; padding:8px 0;">9 AM</td>
                  <td><div class="cell booked">Booked</div></td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell booked">Booked</div></td>
                  <td><div class="cell avail">Open</div></td>
                </tr>
                <tr>
                  <td style="font-size:11px; color:var(--faint); font-weight:600; text-align:center; padding:8px 0;">10 AM</td>
                  <td><div class="cell booked">Booked</div></td>
                  <td><div class="cell booked">Booked</div></td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell avail">Open</div></td>
                </tr>
                <tr>
                  <td style="font-size:11px; color:var(--faint); font-weight:600; text-align:center; padding:8px 0;">1 PM</td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell blocked">—</div></td>
                  <td><div class="cell booked">Booked</div></td>
                  <td><div class="cell avail">Open</div></td>
                </tr>
                <tr>
                  <td style="font-size:11px; color:var(--faint); font-weight:600; text-align:center; padding:8px 0;">2 PM</td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell blocked">—</div></td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell avail">Open</div></td>
                  <td><div class="cell blocked">—</div></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
function showTab(btn, paneId){
  const bar = btn.parentElement;
  bar.querySelectorAll('button').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.tabpane').forEach(p => p.style.display = (p.id === paneId ? 'block' : 'none'));
}
</script>

</body>
</html>
