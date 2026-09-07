<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

$user = getUserProfile($_SESSION['user_id']);

if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    markNotificationAsRead(intval($_GET['mark_read']), $_SESSION['user_id']);
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    redirect('student/notifications.php');
}

if (isset($_GET['mark_all_read'])) {
    $unread = getStudentNotifications($_SESSION['user_id'], true);
    foreach ($unread as $n) {
        markNotificationAsRead($n['id'], $_SESSION['user_id']);
    }
    redirect('student/notifications.php');
}

$all_notifications = getStudentNotifications($_SESSION['user_id']);
$unread_notifications = getStudentNotifications($_SESSION['user_id'], true);
$unread_count = count($unread_notifications);

$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Notifications — GuideSched — Cagasat High School';
$active_page = 'notifications';
$base_url_path = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/head.php'; ?>
    <style>
      .notif-item {
        cursor: pointer;
        transition: background 0.2s ease;
      }
      .notif-item:hover {
        background: var(--violet-50);
      }
    </style>
</head>
<body>

<?php include '../includes/icons.php'; ?>

<div class="app">
  <?php include '../includes/student_sidebar.php'; ?>

  <div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <h1>Notifications</h1>
        <div class="sub">Updates about your guidance appointments</div>
      </div>
      <div class="topbar-right">
        <?php if ($unread_count > 0): ?>
          <a href="notifications.php?mark_all_read=1" class="btn btn-outline btn-sm">Mark all read</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- CONTENT BODY -->
    <div class="content">
      <div class="card">
        <?php if (empty($all_notifications)): ?>
          <div class="empty-note">No notifications found.</div>
        <?php else: ?>
          <?php foreach ($all_notifications as $notif): 
            $icon_class = 'violet';
            $icon_name = '#i-bell';
            if ($notif['type'] === 'approved') { $icon_class = 'green'; $icon_name = '#i-check'; }
            elseif ($notif['type'] === 'declined') { $icon_class = 'red'; $icon_name = '#i-x'; }
            elseif ($notif['type'] === 'rescheduled') { $icon_class = 'amber'; $icon_name = '#i-refresh'; }
            elseif ($notif['type'] === 'reminder') { $icon_class = 'violet'; $icon_name = '#i-clock'; }

            $json_data = htmlspecialchars(json_encode($notif), ENT_QUOTES, 'UTF-8');
          ?>
            <div class="notif-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>" id="notif-row-<?php echo $notif['id']; ?>" onclick="openNotifModal(<?php echo $json_data; ?>)">
              <div class="unread-dot" id="dot-<?php echo $notif['id']; ?>" style="<?php echo $notif['is_read'] ? 'visibility:hidden' : ''; ?>"></div>
              <div class="notif-icon <?php echo $icon_class; ?>">
                <svg width="18" height="18"><use href="<?php echo $icon_name; ?>"/></svg>
              </div>
              <div style="flex:1;">
                <div class="n-title"><?php echo ucfirst($notif['type']); ?> Session Update</div>
                <div class="n-sub"><?php echo htmlspecialchars($notif['message']); ?></div>
                <div style="margin-top:4px; font-size:11.5px; color:var(--violet-600); font-weight:600;">
                  Click to view full appointment details ➜
                </div>
              </div>
              <div class="n-time"><?php echo formatDate($notif['created_at'], 'M j, g:i A'); ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- RICH NOTIFICATION DETAILS MODAL FOR STUDENTS -->
<div id="notifModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(33,27,54,0.6); z-index:99999; align-items:center; justify-content:center; padding:16px;">
  <div style="background:#fff; border-radius:16px; max-width:500px; width:100%; padding:24px; box-shadow:0 20px 40px rgba(0,0,0,0.25); position:relative;">
    <button onclick="closeNotifModal()" style="position:absolute; top:16px; right:16px; background:none; border:none; font-size:22px; font-weight:700; color:#726C87; cursor:pointer;">&times;</button>
    
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
      <div id="modalIconWrap" style="width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center;">
        <svg width="22" height="22"><use id="modalIcon" href="#i-bell"/></svg>
      </div>
      <div>
        <h3 id="modalTitle" style="font-size:18px; color:var(--violet-950); margin:0;">Session Update</h3>
        <div id="modalTime" style="font-size:12px; color:var(--muted);"></div>
      </div>
    </div>

    <div style="background:var(--violet-50); border-radius:12px; padding:14px; margin-bottom:16px; font-size:13.5px; color:var(--ink); line-height:1.5;" id="modalMessage">
    </div>

    <div style="border-top:1px solid var(--line); padding-top:14px; margin-bottom:16px;">
      <h4 style="font-size:13px; text-transform:uppercase; letter-spacing:0.5px; color:var(--muted); margin-bottom:10px;">Appointment Details</h4>
      <table style="width:100%; font-size:13px; border-collapse:collapse;">
        <tr>
          <td style="padding:4px 0; color:var(--muted); width:130px;">Counselor:</td>
          <td style="padding:4px 0; font-weight:700; color:var(--ink);" id="modalCounselorName">N/A</td>
        </tr>
        <tr>
          <td style="padding:4px 0; color:var(--muted);">Specialization:</td>
          <td style="padding:4px 0; font-weight:600;" id="modalCounselorSpec">N/A</td>
        </tr>
        <tr>
          <td style="padding:4px 0; color:var(--muted);">Appointment Date:</td>
          <td style="padding:4px 0; font-weight:600; color:var(--violet-700);" id="modalAptDate">N/A</td>
        </tr>
        <tr>
          <td style="padding:4px 0; color:var(--muted);">Time Slot:</td>
          <td style="padding:4px 0; font-weight:600;" id="modalAptTime">N/A</td>
        </tr>
        <tr>
          <td style="padding:4px 0; color:var(--muted);">Concern Topic:</td>
          <td style="padding:4px 0;" id="modalAptConcern"><span class="tag">N/A</span></td>
        </tr>
        <tr>
          <td style="padding:4px 0; color:var(--muted);">Status:</td>
          <td style="padding:4px 0;" id="modalAptStatus"><span class="pill pending">Pending</span></td>
        </tr>
      </table>
    </div>

    <div style="display:flex; gap:10px; justify-content:flex-end;">
      <a href="appointments.php" class="btn btn-primary btn-sm">Go to My Appointments</a>
      <button onclick="closeNotifModal()" class="btn btn-ghost btn-sm">Close</button>
    </div>
  </div>
</div>

<script>
function openNotifModal(data) {
  // Mark as read asynchronously
  if (!data.is_read) {
    fetch(`notifications.php?mark_read=${data.id}&ajax=1`)
      .then(() => {
        const dot = document.getElementById(`dot-${data.id}`);
        if (dot) dot.style.visibility = 'hidden';
        const row = document.getElementById(`notif-row-${data.id}`);
        if (row) row.classList.remove('unread');
      }).catch(err => console.error(err));
  }

  document.getElementById('modalTitle').innerText = (data.type ? data.type.charAt(0).toUpperCase() + data.type.slice(1) : 'Session') + ' Notification';
  document.getElementById('modalTime').innerText = data.created_at || '';
  document.getElementById('modalMessage').innerText = data.message || '';

  document.getElementById('modalCounselorName').innerText = data.counselor_name || 'Guidance Counselor';
  document.getElementById('modalCounselorSpec').innerText = data.counselor_specialization || 'Guidance Office';
  
  if (data.appointment_date) {
    document.getElementById('modalAptDate').innerText = data.appointment_date;
    document.getElementById('modalAptTime').innerText = (data.start_time || '') + ' - ' + (data.end_time || '');
  } else {
    document.getElementById('modalAptDate').innerText = 'N/A';
    document.getElementById('modalAptTime').innerText = 'N/A';
  }

  document.getElementById('modalAptConcern').innerHTML = `<span class="tag">${data.concern || 'General Guidance'}</span>`;
  
  const status = data.appointment_status || 'pending';
  let statusClass = 'pending';
  if (status === 'approved' || status === 'completed') statusClass = 'confirmed';
  if (status === 'cancelled' || status === 'declined' || status === 'no_show') statusClass = 'cancelled';
  document.getElementById('modalAptStatus').innerHTML = `<span class="pill ${statusClass}">${status.toUpperCase()}</span>`;

  const modalIconWrap = document.getElementById('modalIconWrap');
  const modalIcon = document.getElementById('modalIcon');
  if (data.type === 'approved') {
    modalIconWrap.style.background = 'var(--green-bg)';
    modalIconWrap.style.color = 'var(--green)';
    modalIcon.setAttribute('href', '#i-check');
  } else if (data.type === 'declined') {
    modalIconWrap.style.background = 'var(--red-bg)';
    modalIconWrap.style.color = 'var(--red)';
    modalIcon.setAttribute('href', '#i-x');
  } else {
    modalIconWrap.style.background = 'var(--violet-100)';
    modalIconWrap.style.color = 'var(--violet-700)';
    modalIcon.setAttribute('href', '#i-bell');
  }

  document.getElementById('notifModal').style.display = 'flex';
}

function closeNotifModal() {
  document.getElementById('notifModal').style.display = 'none';
}
</script>

</body>
</html>
