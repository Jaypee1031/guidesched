<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

$user = getUserProfile($_SESSION['user_id']);
$error = '';
$success = '';

// Handle appointment cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $appointment_id = intval($_GET['cancel']);
    $result = cancelAppointment($appointment_id, $_SESSION['user_id']);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

// Handle new booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_submit'])) {
    $counselor_id = intval($_POST['counselor_id']);
    $date = sanitizeInput($_POST['date']);
    $start_time = sanitizeInput($_POST['start_time']);
    $end_time = sanitizeInput($_POST['end_time']);
    $mode = sanitizeInput($_POST['mode'] ?? 'Face-to-face');
    $concern_category = sanitizeInput($_POST['concern_category'] ?? 'Academic stress');
    $details = sanitizeInput($_POST['details'] ?? '');
    
    $full_concern = "[" . $mode . "] " . $concern_category . ($details ? ": " . $details : "");
    
    $result = bookAppointment($_SESSION['user_id'], $counselor_id, $date, $start_time, $end_time, $full_concern);
    if ($result['success']) {
        $success = $result['message'];
        $active_tab = 'upcoming';
    } else {
        $error = $result['message'];
        $active_tab = 'book';
    }
}

// Get appointments
$all_appointments = getStudentAppointments($_SESSION['user_id']);

$upcoming_appointments = [];
$past_appointments = [];

foreach ($all_appointments as $apt) {
    if (in_array($apt['status'], ['pending', 'approved'])) {
        $upcoming_appointments[] = $apt;
    } else {
        $past_appointments[] = $apt;
    }
}

$counselors = getAvailableCounselors();
$active_tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : (isset($active_tab) ? $active_tab : 'upcoming');

$unread_count = count(getStudentNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Appointments — GuideSched — Cagasat High School';
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
  <?php include '../includes/student_sidebar.php'; ?>

  <div class="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <div>
        <h1>Appointments</h1>
        <div class="sub">Book a new session or manage your existing ones</div>
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
      <?php if ($error): ?>
        <div class="alert-box alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert-box alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <!-- TABBAR -->
      <div class="tabbar">
        <button class="<?php echo $active_tab === 'upcoming' ? 'active' : ''; ?>" onclick="showTab(this, 'st-upcoming')">Upcoming (<?php echo count($upcoming_appointments); ?>)</button>
        <button class="<?php echo $active_tab === 'book' ? 'active' : ''; ?>" onclick="showTab(this, 'st-book')">Book New</button>
        <button class="<?php echo $active_tab === 'past' ? 'active' : ''; ?>" onclick="showTab(this, 'st-past')">Past</button>
      </div>

      <!-- UPCOMING TAB -->
      <div id="st-upcoming" class="tabpane" style="display: <?php echo $active_tab === 'upcoming' ? 'block' : 'none'; ?>;">
        <div class="card">
          <?php if (empty($upcoming_appointments)): ?>
            <div class="empty-note">
              No upcoming appointments. <a href="javascript:void(0)" onclick="switchTabDirect('st-book')" class="link-btn">Book an appointment now</a>.
            </div>
          <?php else: ?>
            <?php foreach ($upcoming_appointments as $apt): ?>
              <div class="row-item">
                <div class="time-block">
                  <div class="t"><?php echo date('g:i A', strtotime($apt['start_time'])); ?></div>
                  <div class="d"><?php echo date('M j', strtotime($apt['appointment_date'])); ?></div>
                </div>
                <div class="info">
                  <div class="title"><?php echo htmlspecialchars($apt['counselor_name']); ?></div>
                  <div class="sub"><?php echo htmlspecialchars($apt['concern']); ?></div>
                </div>
                <span class="pill <?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span>
                <div class="actions">
                  <a href="appointments.php?cancel=<?php echo $apt['id']; ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Cancel this appointment?');">Cancel</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- BOOK NEW TAB -->
      <div id="st-book" class="tabpane" style="display: <?php echo $active_tab === 'book' ? 'block' : 'none'; ?>;">
        <form method="POST" action="appointments.php?tab=book" id="bookForm">
          <input type="hidden" name="book_submit" value="1">
          <input type="hidden" name="start_time" id="start_time_input">
          <input type="hidden" name="end_time" id="end_time_input">
          <input type="hidden" name="mode" id="mode_input" value="Face-to-face">
          <input type="hidden" name="concern_category" id="concern_input" value="Academic stress">

          <div class="grid cols-2">
            <!-- LEFT: COUNSELOR & DATE & SLOT GRID -->
            <div class="card">
              <h3 style="margin-bottom:14px;">1. Choose Counselor & Time Slot</h3>
              
              <div class="field" style="margin-bottom:12px;">
                <label>Guidance Counselor</label>
                <select name="counselor_id" id="counselor_select" onchange="loadTimeSlots()" required>
                  <?php foreach ($counselors as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?> (<?php echo htmlspecialchars($c['specialization']); ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="field" style="margin-bottom:14px;">
                <label>Select Date</label>
                <input type="date" name="date" id="date_input" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" onchange="loadTimeSlots()" required>
              </div>

              <label style="display:block; font-size:12px; font-weight:700; color:var(--muted); margin-bottom:8px;">Available Time Slots (Click to Select)</label>
              <div id="slot_grid_container" class="slot-grid">
                <!-- Slot items populated via JS -->
              </div>
              <p id="slot_note" style="font-size:11.5px;color:var(--faint);margin-top:12px;">Select a slot above.</p>
            </div>

            <!-- RIGHT: DETAILS & CONFIRMATION -->
            <div class="card">
              <h3 style="margin-bottom:14px;">2. Session Options & Details</h3>
              
              <div class="field" style="margin-bottom:14px;">
                <label>Counseling Mode (Click to Select)</label>
                <div class="chip-group">
                  <div class="chip active" onclick="selectChip(this, 'mode_input', 'Face-to-face')">Face-to-face (Guidance Office)</div>
                  <div class="chip" onclick="selectChip(this, 'mode_input', 'Online Session')">Online Session</div>
                </div>
              </div>

              <div class="field" style="margin-bottom:14px;">
                <label>What would you like to discuss? (Click Topic)</label>
                <div class="chip-group">
                  <div class="chip active" onclick="selectChip(this, 'concern_input', 'Academic stress')">Academic stress</div>
                  <div class="chip" onclick="selectChip(this, 'concern_input', 'Anxiety & Wellness')">Anxiety & Wellness</div>
                  <div class="chip" onclick="selectChip(this, 'concern_input', 'Family concerns')">Family concerns</div>
                  <div class="chip" onclick="selectChip(this, 'concern_input', 'Peer relationships')">Peer relationships</div>
                  <div class="chip" onclick="selectChip(this, 'concern_input', 'Career & Strand guidance')">Career & Strand guidance</div>
                  <div class="chip" onclick="selectChip(this, 'concern_input', 'Prefer not to say')">Prefer not to say</div>
                </div>
              </div>

              <div class="field" style="margin-bottom:14px;">
                <label>Additional Notes / Quick Topics (Optional)</label>
                <div style="margin-bottom:8px;">
                  <span class="quick-tag" onclick="appendTag('Exam Preparation')">+ Exam Prep</span>
                  <span class="quick-tag" onclick="appendTag('Senior High Strand Selection')">+ Strand Selection</span>
                  <span class="quick-tag" onclick="appendTag('Classroom Stress')">+ Class Stress</span>
                  <span class="quick-tag" onclick="appendTag('Personal Counseling')">+ Personal Wellness</span>
                </div>
                <textarea name="details" id="details_input" rows="3" placeholder="Share any specific notes or click quick tags above..."></textarea>
              </div>

              <button type="submit" id="confirm_btn" class="btn btn-primary" style="width:100%; justify-content:center;" disabled>Confirm Booking</button>
            </div>
          </div>
        </form>
      </div>

      <!-- PAST TAB -->
      <div id="st-past" class="tabpane" style="display: <?php echo $active_tab === 'past' ? 'block' : 'none'; ?>;">
        <div class="card">
          <?php if (empty($past_appointments)): ?>
            <div class="empty-note">No past appointment records.</div>
          <?php else: ?>
            <?php foreach ($past_appointments as $apt): ?>
              <div class="row-item">
                <div class="time-block">
                  <div class="t"><?php echo date('g:i A', strtotime($apt['start_time'])); ?></div>
                  <div class="d"><?php echo date('M j, Y', strtotime($apt['appointment_date'])); ?></div>
                </div>
                <div class="info">
                  <div class="title"><?php echo htmlspecialchars($apt['counselor_name']); ?></div>
                  <div class="sub"><span class="tag"><?php echo htmlspecialchars($apt['concern']); ?></span></div>
                </div>
                <span class="pill <?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
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

function switchTabDirect(paneId){
  const btn = document.querySelector(`.tabbar button[onclick*="${paneId}"]`);
  if(btn) showTab(btn, paneId);
}

function selectChip(el, inputId, val){
  el.parentElement.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  document.getElementById(inputId).value = val;
}

function appendTag(txt){
  const textarea = document.getElementById('details_input');
  if(textarea.value.trim() === ""){
    textarea.value = txt;
  } else {
    textarea.value += ", " + txt;
  }
}

function loadTimeSlots(){
  const counselorId = document.getElementById('counselor_select').value;
  const date = document.getElementById('date_input').value;
  const container = document.getElementById('slot_grid_container');
  const note = document.getElementById('slot_note');
  const confirmBtn = document.getElementById('confirm_btn');

  if(!counselorId || !date){
    container.innerHTML = '<div style="grid-column:span 4;color:var(--faint);font-size:12px;">Select counselor and date</div>';
    return;
  }

  container.innerHTML = '<div style="grid-column:span 4;color:var(--muted);font-size:12px;">Loading slots...</div>';

  fetch(`get-availability.php?counselor_id=${counselorId}&date=${date}`)
    .then(r => r.json())
    .then(data => {
      if(data.success && data.slots && data.slots.length > 0){
        let html = '';
        data.slots.forEach(slot => {
          const formatted = formatTimeStr(slot.start_time);
          const isTaken = (slot.status === 'booked' || slot.status === 'blocked');
          if(isTaken){
            html += `<div class="slot taken" title="This slot is unavailable">${formatted}</div>`;
          } else {
            html += `<div class="slot" data-start="${slot.start_time}" data-end="${slot.end_time}" onclick="selectSlot(this, '${formatted}')">${formatted}</div>`;
          }
        });
        container.innerHTML = html;
        note.textContent = 'Click on an open time slot above to select.';
      } else {
        container.innerHTML = '<div style="grid-column:span 4;color:var(--muted);font-size:12px;">No slots available for this date.</div>';
        note.textContent = 'Please select another date.';
      }
    })
    .catch(err => {
      console.error(err);
      container.innerHTML = '<div style="grid-column:span 4;color:var(--red);font-size:12px;">Error loading slots</div>';
    });
}

function selectSlot(el, label){
  document.querySelectorAll('.slot').forEach(s => s.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('start_time_input').value = el.dataset.start;
  document.getElementById('end_time_input').value = el.dataset.end;
  document.getElementById('slot_note').textContent = `Selected Slot: ${label}`;
  document.getElementById('confirm_btn').disabled = false;
}

function formatTimeStr(tStr){
  const parts = tStr.split(':');
  let h = parseInt(parts[0]);
  const m = parts[1];
  const ampm = h >= 12 ? 'PM' : 'AM';
  h = h % 12 || 12;
  return `${h}:${m} ${ampm}`;
}

document.addEventListener('DOMContentLoaded', loadTimeSlots);
</script>

</body>
</html>
