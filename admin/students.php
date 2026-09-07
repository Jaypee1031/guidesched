<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$grade_filter = isset($_GET['grade']) ? sanitizeInput($_GET['grade']) : '';

$error = '';
$success = '';

// Handle actions: delete, toggle_status, add_student, edit_student
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $target_id = intval($_GET['id']);
    $action = sanitizeInput($_GET['action']);
    
    if ($action === 'delete') {
        $res = deleteStudentAccount($target_id);
        if ($res['success']) { $success = $res['message']; } else { $error = $res['message']; }
    } elseif ($action === 'toggle_status') {
        $res = toggleStudentStatus($target_id);
        if ($res['success']) { $success = $res['message']; } else { $error = $res['message']; }
    }
}

// Handle Add Student form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $dept_grade = sanitizeInput($_POST['course']);
    $year_lvl = 7;
    if (strpos($dept_grade, 'Grade 8') !== false) { $year_lvl = 8; }
    elseif (strpos($dept_grade, 'Grade 9') !== false) { $year_lvl = 9; }
    elseif (strpos($dept_grade, 'Grade 10') !== false) { $year_lvl = 10; }
    elseif (strpos($dept_grade, 'Grade 11') !== false) { $year_lvl = 11; }
    elseif (strpos($dept_grade, 'Grade 12') !== false) { $year_lvl = 12; }

    $data = [
        'name' => sanitizeInput($_POST['name']),
        'email' => sanitizeInput($_POST['email']),
        'password' => $_POST['password'],
        'student_number' => sanitizeInput($_POST['student_number']),
        'course' => $dept_grade,
        'year_level' => $year_lvl,
        'contact_number' => sanitizeInput($_POST['contact_number'])
    ];
    
    $res = registerStudent($data);
    if ($res['success']) { $success = 'Student account created successfully!'; } else { $error = $res['message']; }
}

// Handle Edit Student form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $edit_id = intval($_POST['student_id']);
    $dept_grade = sanitizeInput($_POST['course']);
    $year_lvl = 7;
    if (strpos($dept_grade, 'Grade 8') !== false) { $year_lvl = 8; }
    elseif (strpos($dept_grade, 'Grade 9') !== false) { $year_lvl = 9; }
    elseif (strpos($dept_grade, 'Grade 10') !== false) { $year_lvl = 10; }
    elseif (strpos($dept_grade, 'Grade 11') !== false) { $year_lvl = 11; }
    elseif (strpos($dept_grade, 'Grade 12') !== false) { $year_lvl = 12; }

    $data = [
        'name' => sanitizeInput($_POST['name']),
        'email' => sanitizeInput($_POST['email']),
        'student_number' => sanitizeInput($_POST['student_number']),
        'course' => $dept_grade,
        'year_level' => $year_lvl,
        'contact_number' => sanitizeInput($_POST['contact_number'])
    ];
    
    $res = adminUpdateStudentProfile($edit_id, $data);
    if ($res['success']) { $success = $res['message']; } else { $error = $res['message']; }
}

$students = getAllStudents($search);

// Filter by grade level if selected
if ($grade_filter) {
    $students = array_filter($students, function($s) use ($grade_filter) {
        return strpos($s['course'] ?? '', $grade_filter) !== false;
    });
}

$selected_student_id = isset($_GET['student_id']) && is_numeric($_GET['student_id']) ? intval($_GET['student_id']) : null;
$student_history = [];
$selected_student = null;

if ($selected_student_id) {
    $student_history = getStudentAppointmentHistory($selected_student_id);
    foreach ($students as $s) {
        if ($s['id'] == $selected_student_id) {
            $selected_student = $s;
            break;
        }
    }
}

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Students — Counselor Portal — GuideSched — Cagasat High School';
$active_page = 'students';
$base_url_path = '../';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include '../includes/head.php'; ?>
    <style>
      .btn-danger-sm {
        background: var(--red-bg);
        color: var(--red);
        border: 1px solid rgba(192, 57, 43, 0.2);
        padding: 4px 8px;
        font-size: 11.5px;
        border-radius: 6px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
      }
      .btn-danger-sm:hover {
        background: var(--red);
        color: #fff;
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
        <h1>Student Management</h1>
        <div class="sub">View student profiles, edit details, toggle status, or remove accounts</div>
      </div>
      <div class="topbar-right">
        <button class="btn btn-primary" onclick="openAddStudentModal()">
          <span class="icon"><svg><use href="#i-plus"/></svg></span>Add New Student
        </button>
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

      <!-- SEARCH & FILTER CARD -->
      <div class="card" style="margin-bottom:16px;">
        <form method="GET" action="" style="display:flex; gap:12px; flex-wrap:wrap;">
          <div style="flex:2; min-width:200px;">
            <input type="text" name="search" placeholder="Search by student name, LRN/ID, or email..." value="<?php echo htmlspecialchars($search); ?>" style="width:100%; padding:10px 14px; border-radius:9px; border:1px solid var(--line); font-size:13.5px;">
          </div>
          <div style="flex:1; min-width:180px;">
            <select name="grade" onchange="this.form.submit()" style="width:100%; padding:10px 12px; border-radius:9px; border:1px solid var(--line); font-size:13.5px;">
              <option value="">All Departments & Grades</option>
              <option value="Grade 7" <?php echo $grade_filter === 'Grade 7' ? 'selected' : ''; ?>>Grade 7</option>
              <option value="Grade 8" <?php echo $grade_filter === 'Grade 8' ? 'selected' : ''; ?>>Grade 8</option>
              <option value="Grade 9" <?php echo $grade_filter === 'Grade 9' ? 'selected' : ''; ?>>Grade 9</option>
              <option value="Grade 10" <?php echo $grade_filter === 'Grade 10' ? 'selected' : ''; ?>>Grade 10</option>
              <option value="Grade 11" <?php echo $grade_filter === 'Grade 11' ? 'selected' : ''; ?>>Grade 11 (Senior High)</option>
              <option value="Grade 12" <?php echo $grade_filter === 'Grade 12' ? 'selected' : ''; ?>>Grade 12 (Senior High)</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Search & Filter</button>
        </form>
      </div>

      <div class="grid cols-<?php echo $selected_student_id ? '2' : '1'; ?>">
        <!-- STUDENTS TABLE -->
        <div class="card">
          <div class="card-head">
            <h3>Registered Students (<?php echo count($students); ?>)</h3>
          </div>

          <?php if (empty($students)): ?>
            <div class="empty-note">No students found matching your criteria.</div>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Student</th>
                  <th>LRN / Student ID</th>
                  <th>Department & Grade</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($students as $s): 
                  $s_json = htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8');
                ?>
                  <tr>
                    <td>
                      <div class="name-cell">
                        <div class="avatar"><?php echo strtoupper(substr($s['name'], 0, 2)); ?></div>
                        <div>
                          <div style="font-weight:700;"><?php echo htmlspecialchars($s['name']); ?></div>
                          <div style="font-size:11.5px; color:var(--muted);"><?php echo htmlspecialchars($s['email']); ?></div>
                        </div>
                      </div>
                    </td>
                    <td><?php echo htmlspecialchars($s['student_number']); ?></td>
                    <td><span class="tag"><?php echo htmlspecialchars($s['course'] ?? 'General Student'); ?></span></td>
                    <td>
                      <a href="students.php?action=toggle_status&id=<?php echo $s['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="pill <?php echo $s['status'] === 'active' ? 'confirmed' : 'pending'; ?>" title="Click to toggle active/inactive status">
                        <?php echo ucfirst($s['status']); ?>
                      </a>
                    </td>
                    <td>
                      <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center;">
                        <a href="students.php?student_id=<?php echo $s['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-ghost btn-sm">History</a>
                        
                        <button onclick="openEditStudentModal(<?php echo $s_json; ?>)" class="btn btn-outline btn-sm" title="Edit Student Information">
                          Edit
                        </button>
                        
                        <a href="students.php?action=delete&id=<?php echo $s['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn-danger-sm" onclick="return confirm('Are you sure you want to permanently remove/delete student account for <?php echo addslashes(htmlspecialchars($s['name'])); ?>? This will delete all their appointment history and notifications.')" title="Permanently remove student account">
                          Remove
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>

        <!-- SELECTED STUDENT DETAILS -->
        <?php if ($selected_student_id && $selected_student): ?>
          <div class="card">
            <div class="card-head">
              <h3>Student Profile & History</h3>
              <a href="students.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" class="link-btn">Close</a>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
              <div style="display:flex; align-items:center; gap:14px;">
                <div class="avatar" style="width:52px;height:52px;font-size:18px;"><?php echo strtoupper(substr($selected_student['name'], 0, 2)); ?></div>
                <div>
                  <h4 style="font-size:16px;"><?php echo htmlspecialchars($selected_student['name']); ?></h4>
                  <div style="color:var(--muted); font-size:12.5px;"><?php echo htmlspecialchars($selected_student['email']); ?></div>
                </div>
              </div>
              <div>
                <a href="students.php?action=delete&id=<?php echo $selected_student['id']; ?>" class="btn-danger-sm" onclick="return confirm('Delete account?')">Remove Account</a>
              </div>
            </div>

            <div class="form-grid" style="margin-bottom:18px;">
              <div><div style="font-size:11px;color:var(--faint);font-weight:700;">LRN / STUDENT ID</div><div style="font-weight:700;font-size:13.5px;"><?php echo htmlspecialchars($selected_student['student_number']); ?></div></div>
              <div><div style="font-size:11px;color:var(--faint);font-weight:700;">GRADE & STRAND</div><div style="font-weight:700;font-size:13.5px;"><?php echo htmlspecialchars($selected_student['course']); ?></div></div>
              <div><div style="font-size:11px;color:var(--faint);font-weight:700;">CONTACT</div><div style="font-weight:700;font-size:13.5px;"><?php echo htmlspecialchars($selected_student['contact_number']); ?></div></div>
              <div><div style="font-size:11px;color:var(--faint);font-weight:700;">REGISTERED</div><div style="font-weight:700;font-size:13.5px;"><?php echo formatDate($selected_student['created_at']); ?></div></div>
            </div>

            <h4 style="font-size:14px; margin-bottom:12px;">Guidance Session History</h4>
            <?php if (empty($student_history)): ?>
              <div class="empty-note">No appointment history for this student.</div>
            <?php else: ?>
              <?php foreach ($student_history as $h): ?>
                <div class="row-item" style="padding:8px 0;">
                  <div class="time-block" style="width:75px;">
                    <div class="t" style="font-size:12px;"><?php echo date('M j', strtotime($h['appointment_date'])); ?></div>
                  </div>
                  <div class="info">
                    <div class="title" style="font-size:12.5px;"><?php echo htmlspecialchars($h['counselor_name']); ?></div>
                    <div class="sub" style="font-size:11.5px;"><?php echo htmlspecialchars($h['concern']); ?></div>
                  </div>
                  <span class="pill <?php echo $h['status']; ?>" style="font-size:10.5px;"><?php echo ucfirst($h['status']); ?></span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<!-- ADD NEW STUDENT MODAL -->
<div id="addStudentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(33,27,54,0.6); z-index:99999; align-items:center; justify-content:center; padding:16px;">
  <div style="background:#fff; border-radius:16px; max-width:480px; width:100%; padding:24px; box-shadow:0 20px 40px rgba(0,0,0,0.25); position:relative;">
    <button onclick="closeAddStudentModal()" style="position:absolute; top:16px; right:16px; background:none; border:none; font-size:22px; font-weight:700; color:#726C87; cursor:pointer;">&times;</button>
    <h3 style="font-size:18px; color:var(--violet-950); margin-bottom:16px;">Add New Student Account</h3>
    
    <form method="POST" action="">
      <input type="hidden" name="add_student" value="1">
      <div class="field" style="margin-bottom:12px;">
        <label>Student Full Name</label>
        <input type="text" name="name" placeholder="e.g. Juan Dela Cruz" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      <div class="field" style="margin-bottom:12px;">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="student@cagasaths.edu.ph" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      <div class="field" style="margin-bottom:12px;">
        <label>Initial Password</label>
        <input type="password" name="password" placeholder="••••••••" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      <div class="field" style="margin-bottom:12px;">
        <label>LRN / Student ID Number</label>
        <input type="text" name="student_number" placeholder="1029384756" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      <div class="field" style="margin-bottom:12px;">
        <label>Department & Grade / Strand</label>
        <select name="course" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
          <option value="Grade 7 (Junior High)">Grade 7 (Junior High)</option>
          <option value="Grade 8 (Junior High)">Grade 8 (Junior High)</option>
          <option value="Grade 9 (Junior High)">Grade 9 (Junior High)</option>
          <option value="Grade 10 (Junior High)">Grade 10 (Junior High)</option>
          <option value="Grade 11 - STEM">Grade 11 - STEM</option>
          <option value="Grade 11 - ABM">Grade 11 - ABM</option>
          <option value="Grade 11 - HUMSS">Grade 11 - HUMSS</option>
          <option value="Grade 11 - TVL">Grade 11 - TVL</option>
          <option value="Grade 12 - STEM">Grade 12 - STEM</option>
          <option value="Grade 12 - ABM">Grade 12 - ABM</option>
          <option value="Grade 12 - HUMSS">Grade 12 - HUMSS</option>
          <option value="Grade 12 - TVL">Grade 12 - TVL</option>
        </select>
      </div>
      <div class="field" style="margin-bottom:18px;">
        <label>Contact Number</label>
        <input type="text" name="contact_number" placeholder="09171234567" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      
      <div style="display:flex; justify-content:flex-end; gap:10px;">
        <button type="button" onclick="closeAddStudentModal()" class="btn btn-ghost">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Student Account</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT STUDENT MODAL -->
<div id="editStudentModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(33,27,54,0.6); z-index:99999; align-items:center; justify-content:center; padding:16px;">
  <div style="background:#fff; border-radius:16px; max-width:480px; width:100%; padding:24px; box-shadow:0 20px 40px rgba(0,0,0,0.25); position:relative;">
    <button onclick="closeEditStudentModal()" style="position:absolute; top:16px; right:16px; background:none; border:none; font-size:22px; font-weight:700; color:#726C87; cursor:pointer;">&times;</button>
    <h3 style="font-size:18px; color:var(--violet-950); margin-bottom:16px;">Edit Student Information</h3>
    
    <form method="POST" action="">
      <input type="hidden" name="edit_student" value="1">
      <input type="hidden" name="student_id" id="edit_student_id">
      
      <div class="field" style="margin-bottom:12px;">
        <label>Student Full Name</label>
        <input type="text" name="name" id="edit_name" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      <div class="field" style="margin-bottom:12px;">
        <label>Email Address</label>
        <input type="email" name="email" id="edit_email" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      <div class="field" style="margin-bottom:12px;">
        <label>LRN / Student ID Number</label>
        <input type="text" name="student_number" id="edit_student_number" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      <div class="field" style="margin-bottom:12px;">
        <label>Department & Grade / Strand</label>
        <select name="course" id="edit_course" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
          <option value="Grade 7 (Junior High)">Grade 7 (Junior High)</option>
          <option value="Grade 8 (Junior High)">Grade 8 (Junior High)</option>
          <option value="Grade 9 (Junior High)">Grade 9 (Junior High)</option>
          <option value="Grade 10 (Junior High)">Grade 10 (Junior High)</option>
          <option value="Grade 11 - STEM">Grade 11 - STEM</option>
          <option value="Grade 11 - ABM">Grade 11 - ABM</option>
          <option value="Grade 11 - HUMSS">Grade 11 - HUMSS</option>
          <option value="Grade 11 - TVL">Grade 11 - TVL</option>
          <option value="Grade 12 - STEM">Grade 12 - STEM</option>
          <option value="Grade 12 - ABM">Grade 12 - ABM</option>
          <option value="Grade 12 - HUMSS">Grade 12 - HUMSS</option>
          <option value="Grade 12 - TVL">Grade 12 - TVL</option>
        </select>
      </div>
      <div class="field" style="margin-bottom:18px;">
        <label>Contact Number</label>
        <input type="text" name="contact_number" id="edit_contact_number" required style="width:100%; padding:8px 12px; border-radius:8px; border:1px solid var(--line);">
      </div>
      
      <div style="display:flex; justify-content:flex-end; gap:10px;">
        <button type="button" onclick="closeEditStudentModal()" class="btn btn-ghost">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddStudentModal() {
  document.getElementById('addStudentModal').style.display = 'flex';
}
function closeAddStudentModal() {
  document.getElementById('addStudentModal').style.display = 'none';
}

function openEditStudentModal(data) {
  document.getElementById('edit_student_id').value = data.id || '';
  document.getElementById('edit_name').value = data.name || '';
  document.getElementById('edit_email').value = data.email || '';
  document.getElementById('edit_student_number').value = data.student_number || '';
  document.getElementById('edit_course').value = data.course || 'Grade 7 (Junior High)';
  document.getElementById('edit_contact_number').value = data.contact_number || '';
  document.getElementById('editStudentModal').style.display = 'flex';
}
function closeEditStudentModal() {
  document.getElementById('editStudentModal').style.display = 'none';
}
</script>

</body>
</html>
