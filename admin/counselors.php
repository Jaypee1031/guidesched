<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';
require_once '../includes/admin_functions.php';

requireRole('admin');

$user = getUserProfile($_SESSION['user_id']);
$error = '';
$success = '';

if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $counselor_id = intval($_GET['id']);
    $action = sanitizeInput($_GET['action']);
    
    $conn = getDBConnection();
    $new_status = ($action === 'activate') ? 'active' : 'inactive';
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'counselor'");
    $stmt->bind_param("si", $new_status, $counselor_id);
    
    if ($stmt->execute()) {
        $success = "Counselor status updated successfully.";
    } else {
        $error = "Failed to update counselor status.";
    }
    
    closeDBConnection($conn);
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT u.id, u.user_id, u.name, u.email, u.status, u.created_at, c.specialization, c.contact_number 
                        FROM users u 
                        LEFT JOIN counselor_profiles c ON u.id = c.user_id 
                        WHERE u.role = 'counselor' 
                        ORDER BY u.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$counselors = $result->fetch_all(MYSQLI_ASSOC);
closeDBConnection($conn);

$unread_count = count(getAdminNotifications($_SESSION['user_id'], true));
$user_initials = strtoupper(substr($user['name'], 0, 1) . (strpos($user['name'], ' ') ? substr(explode(' ', $user['name'])[1], 0, 1) : ''));

$page_title = 'Counselors — Admin Portal — GuideSched — Cagasat High School';
$active_page = 'counselors';
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
        <h1>Counselors Management</h1>
        <div class="sub">Manage guidance counselor profiles and access</div>
      </div>
      <div class="topbar-right">
        <a href="add-counselor.php" class="btn btn-primary">
          <span class="icon"><svg><use href="#i-plus"/></svg></span>Add Counselor
        </a>
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
      <?php if ($error): ?>
        <div class="alert-box alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert-box alert-success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="card-head">
          <h3>Guidance Counselors (<?php echo count($counselors); ?>)</h3>
        </div>

        <?php if (empty($counselors)): ?>
          <div class="empty-note">No counselors registered. Add your first counselor.</div>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th>Counselor</th>
                <th>Specialization</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($counselors as $c): ?>
                <tr>
                  <td>
                    <div class="name-cell">
                      <div class="avatar" style="background:var(--violet-700);"><?php echo strtoupper(substr($c['name'], 0, 2)); ?></div>
                      <div>
                        <div style="font-weight:700;"><?php echo htmlspecialchars($c['name']); ?></div>
                        <div style="font-size:11.5px; color:var(--muted);"><?php echo htmlspecialchars($c['user_id']); ?></div>
                      </div>
                    </div>
                  </td>
                  <td><span class="tag"><?php echo htmlspecialchars($c['specialization'] ?? 'General Counseling'); ?></span></td>
                  <td><?php echo htmlspecialchars($c['email']); ?></td>
                  <td><?php echo htmlspecialchars($c['contact_number'] ?? 'N/A'); ?></td>
                  <td><span class="pill <?php echo $c['status'] === 'active' ? 'confirmed' : 'cancelled'; ?>"><?php echo ucfirst($c['status']); ?></span></td>
                  <td>
                    <?php if ($c['status'] === 'active'): ?>
                      <a href="counselors.php?action=deactivate&id=<?php echo $c['id']; ?>" class="btn btn-decline btn-sm" onclick="return confirm('Deactivate counselor account?');">Deactivate</a>
                    <?php else: ?>
                      <a href="counselors.php?action=activate&id=<?php echo $c['id']; ?>" class="btn btn-approve btn-sm" onclick="return confirm('Activate counselor account?');">Activate</a>
                    <?php endif; ?>
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

</body>
</html>
