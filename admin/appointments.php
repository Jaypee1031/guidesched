<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

$error = '';
$success = '';

// Handle appointment actions
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $action = sanitizeInput($_GET['action']);
    
    if ($action === 'approve') {
        $result = updateAppointmentStatus($appointment_id, 'approved', $_SESSION['user_id']);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'decline') {
        $result = updateAppointmentStatus($appointment_id, 'declined', $_SESSION['user_id']);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'complete') {
        $result = updateAppointmentStatus($appointment_id, 'completed', $_SESSION['user_id']);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    } elseif ($action === 'noshow') {
        $result = updateAppointmentStatus($appointment_id, 'no_show', $_SESSION['user_id']);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

// Handle notes update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_notes'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $admin_notes = sanitizeInput($_POST['admin_notes']);
    
    $result = updateAppointmentStatus($appointment_id, $_POST['current_status'], $_SESSION['user_id'], $admin_notes);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

// Get filters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$counselor_filter = isset($_GET['counselor']) && is_numeric($_GET['counselor']) ? intval($_GET['counselor']) : null;
$date_from = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';

// Get appointments
$appointments = getAllAppointments($status_filter, $counselor_filter, $date_from, $date_to);

// Get counselors for filter
$counselors = getAvailableCounselors();
$user = getUserProfile($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Management - GuideSched</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .sidebar .nav-link i {
            width: 25px;
        }
        .main-content {
            padding: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        .badge-pending {
            background: #ffc107;
            color: #000;
        }
        .badge-approved {
            background: #28a745;
        }
        .badge-declined {
            background: #dc3545;
        }
        .badge-completed {
            background: #17a2b8;
        }
        .badge-cancelled {
            background: #6c757d;
        }
        .badge-rescheduled {
            background: #fd7e14;
        }
        .badge-no_show {
            background: #6f42c1;
        }
        .filter-section {
            background: #f0fff4;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .action-btn {
            padding: 5px 10px;
            font-size: 0.8rem;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4><i class="fas fa-calendar-check me-2"></i>GuideSched</h4>
                    <small class="d-block mt-2 opacity-75"><?php echo ucfirst($user['role']); ?> Portal</small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="nav-link active" href="appointments.php">
                        <i class="fas fa-calendar-alt"></i> Appointments
                    </a>
                    <a class="nav-link" href="schedule.php">
                        <i class="fas fa-clock"></i> Schedule Management
                    </a>
                    <a class="nav-link" href="students.php">
                        <i class="fas fa-users"></i> Students
                    </a>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a class="nav-link" href="counselors.php">
                        <i class="fas fa-user-tie"></i> Counselors
                    </a>
                    <a class="nav-link" href="analytics.php">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-file-alt"></i> Reports
                    </a>
                    <?php endif; ?>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="card p-4">
                    <h2 class="mb-4">Appointment Management</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filter Section -->
                    <div class="filter-section">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="declined" <?php echo $status_filter === 'declined' ? 'selected' : ''; ?>>Declined</option>
                                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="no_show" <?php echo $status_filter === 'no_show' ? 'selected' : ''; ?>>No Show</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="counselor" class="form-label">Counselor</label>
                                    <select class="form-select" id="counselor" name="counselor">
                                        <option value="">All Counselors</option>
                                        <?php foreach ($counselors as $counselor): ?>
                                        <option value="<?php echo $counselor['id']; ?>" <?php echo $counselor_filter == $counselor['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($counselor['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                                </div>
                                <div class="col-md-2 mb-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <?php if (empty($appointments)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No appointments found matching the selected criteria.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Student</th>
                                        <th>Counselor</th>
                                        <th>Concern</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                        <td><?php echo formatTime($appointment['start_time']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['counselor_name']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($appointment['concern'], 0, 30)) . '...'; ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $appointment['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $appointment['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <?php if ($appointment['status'] === 'pending'): ?>
                                                    <a href="appointments.php?action=approve&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-success action-btn">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <a href="appointments.php?action=decline&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-danger action-btn">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($appointment['status'] === 'approved'): ?>
                                                    <a href="appointments.php?action=complete&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-info action-btn">
                                                        <i class="fas fa-check-double"></i>
                                                    </a>
                                                    <a href="appointments.php?action=noshow&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-warning action-btn">
                                                        <i class="fas fa-user-slash"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <button type="button" class="btn btn-sm btn-secondary action-btn" data-bs-toggle="modal" data-bs-target="#notesModal<?php echo $appointment['id']; ?>">
                                                    <i class="fas fa-sticky-note"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Notes Modal -->
                                    <div class="modal fade" id="notesModal<?php echo $appointment['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Appointment Notes</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="update_notes" value="1">
                                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                        <input type="hidden" name="current_status" value="<?php echo $appointment['status']; ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label">Date & Time</label>
                                                            <p class="form-control-plaintext"><?php echo formatDate($appointment['appointment_date']) . ' at ' . formatTime($appointment['start_time']); ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Student</label>
                                                            <p class="form-control-plaintext"><?php echo htmlspecialchars($appointment['student_name']); ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Concern</label>
                                                            <p class="form-control-plaintext"><?php echo htmlspecialchars($appointment['concern']); ?></p>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="admin_notes" class="form-label">Admin Notes</label>
                                                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="4"><?php echo htmlspecialchars($appointment['admin_notes'] ?? ''); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save Notes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <p class="text-muted">Showing <?php echo count($appointments); ?> appointment(s)</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
