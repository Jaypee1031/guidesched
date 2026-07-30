<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

$error = '';
$success = '';

// Handle cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $appointment_id = intval($_GET['cancel']);
    $result = cancelAppointment($appointment_id, $_SESSION['user_id']);
    
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}

// Get filter status
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Get appointments
$appointments = $status_filter ? getStudentAppointments($_SESSION['user_id'], $status_filter) : getStudentAppointments($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - GuideSched</title>
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
        .btn-book {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            color: white;
        }
        .btn-book:hover {
            color: white;
            transform: translateY(-2px);
        }
        .filter-btn {
            border-radius: 20px;
            padding: 8px 20px;
            border: 1px solid #e0e0e0;
            background: white;
            color: #666;
            transition: all 0.3s;
        }
        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-color: transparent;
        }
        .appointment-card {
            border-left: 4px solid #11998e;
            transition: all 0.3s;
        }
        .appointment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a class="nav-link" href="book-appointment.php">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </a>
                    <a class="nav-link active" href="appointments.php">
                        <i class="fas fa-calendar-alt"></i> My Appointments
                    </a>
                    <a class="nav-link" href="notifications.php">
                        <i class="fas fa-bell"></i> Notifications
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>My Appointments</h2>
                        <a href="book-appointment.php" class="btn btn-book">
                            <i class="fas fa-plus me-2"></i>Book New Appointment
                        </a>
                    </div>
                    
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
                    
                    <!-- Filter Buttons -->
                    <div class="mb-4">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="appointments.php" class="filter-btn <?php echo $status_filter === '' ? 'active' : ''; ?>">
                                All
                            </a>
                            <a href="appointments.php?status=pending" class="filter-btn <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                                Pending
                            </a>
                            <a href="appointments.php?status=approved" class="filter-btn <?php echo $status_filter === 'approved' ? 'active' : ''; ?>">
                                Approved
                            </a>
                            <a href="appointments.php?status=completed" class="filter-btn <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                                Completed
                            </a>
                            <a href="appointments.php?status=cancelled" class="filter-btn <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                                Cancelled
                            </a>
                        </div>
                    </div>
                    
                    <?php if (empty($appointments)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php if ($status_filter): ?>
                                No <?php echo $status_filter; ?> appointments found.
                            <?php else: ?>
                                No appointments found. <a href="book-appointment.php">Book your first appointment</a> to get started.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($appointments as $appointment): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card appointment-card p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1"><?php echo formatDate($appointment['appointment_date']); ?></h5>
                                            <p class="text-muted mb-0">
                                                <i class="fas fa-clock me-1"></i><?php echo formatTime($appointment['start_time']); ?> - <?php echo formatTime($appointment['end_time']); ?>
                                            </p>
                                        </div>
                                        <span class="badge badge-<?php echo $appointment['status']; ?>">
                                            <?php echo ucfirst($appointment['status']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong><i class="fas fa-user-tie me-2"></i>Counselor:</strong>
                                        <?php echo htmlspecialchars($appointment['counselor_name']); ?>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <strong><i class="fas fa-comment me-2"></i>Concern:</strong>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars(substr($appointment['concern'], 0, 100)) . (strlen($appointment['concern']) > 100 ? '...' : ''); ?></p>
                                    </div>
                                    
                                    <?php if ($appointment['admin_notes']): ?>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-sticky-note me-2"></i>Notes:</strong>
                                        <p class="mb-0 text-muted"><?php echo htmlspecialchars($appointment['admin_notes']); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (in_array($appointment['status'], ['pending', 'approved'])): ?>
                                    <div class="mt-3">
                                        <a href="appointments.php?cancel=<?php echo $appointment['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to cancel this appointment?');">
                                            <i class="fas fa-times me-1"></i>Cancel Appointment
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
