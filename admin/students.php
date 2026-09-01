<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$user = getUserProfile($_SESSION['user_id']);
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$grade_filter = isset($_GET['grade']) ? sanitizeInput($_GET['grade']) : '';

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

$page_title = 'Students — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'students';
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
        <h1>Student Management</h1>
        <div class="sub">View student profiles and guidance appointment history</div>
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
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($students as $s): ?>
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
                    <td><span class="pill confirmed"><?php echo ucfirst($s['status']); ?></span></td>
                    <td>
                      <a href="students.php?student_id=<?php echo $s['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn btn-ghost btn-sm">View Profile</a>
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
              <h3>Student Profile Overview</h3>
              <a href="students.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" class="link-btn">Close</a>
            </div>

            <div style="display:flex; align-items:center; gap:14px; margin-bottom:18px;">
              <div class="avatar" style="width:52px;height:52px;font-size:18px;"><?php echo strtoupper(substr($selected_student['name'], 0, 2)); ?></div>
              <div>
                <h4 style="font-size:16px;"><?php echo htmlspecialchars($selected_student['name']); ?></h4>
                <div style="color:var(--muted); font-size:12.5px;"><?php echo htmlspecialchars($selected_student['email']); ?></div>
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

</body>
</html>
