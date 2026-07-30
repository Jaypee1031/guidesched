<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/admin_functions.php';

requireAnyRole(['admin', 'counselor']);

// Get user profile
$user = getUserProfile($_SESSION['user_id']);

// Get admin statistics
$stats = getAdminStatistics();

// Get recent appointments
$recent_appointments = array_slice(getAllAppointments(), 0, 5);

// Get pending requests
$pending_requests = getAllAppointments('pending');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GuideSched</title>
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
        .stat-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
        }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        .stat-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.warning {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stat-card.info {
            background: linear-gradient(135deg, #1fa463 0%, #4ade80 100%);
        }
        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
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
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="nav-link" href="appointments.php">
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
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                            <p class="text-muted mb-0"><?php echo ucfirst($user['role']); ?> Portal</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="user-avatar d-inline-flex">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?php echo $stats['total_students']; ?></h3>
                            <p>Total Students</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card success">
                            <h3><?php echo $stats['total_appointments']; ?></h3>
                            <p>Total Appointments</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card warning">
                            <h3><?php echo $stats['pending_requests']; ?></h3>
                            <p>Pending Requests</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card info">
                            <h3><?php echo $stats['completed_sessions']; ?></h3>
                            <p>Completed Sessions</p>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card p-4">
                            <h4 class="mb-4">Additional Statistics</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h3><?php echo $stats['approved_appointments']; ?></h3>
                                        <small class="text-muted">Approved</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h3><?php echo $stats['cancelled_appointments']; ?></h3>
                                        <small class="text-muted">Cancelled</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h3><?php echo $stats['no_shows']; ?></h3>
                                        <small class="text-muted">No Shows</small>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h3><?php echo $stats['total_counselors']; ?></h3>
                                        <small class="text-muted">Counselors</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-4">
                            <h4 class="mb-4">Recent Appointments</h4>
                            <?php if (empty($recent_appointments)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No recent appointments to display.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Student</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_appointments as $appointment): ?>
                                            <tr>
                                                <td><?php echo formatDate($appointment['appointment_date'], 'M j'); ?></td>
                                                <td><?php echo htmlspecialchars($appointment['student_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $appointment['status'] === 'approved' ? 'success' : ($appointment['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All Appointments</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($pending_requests) && $user['role'] === 'admin'): ?>
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card p-4 border-warning">
                            <h4 class="mb-4 text-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>Pending Requests Requiring Attention
                            </h4>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Student</th>
                                            <th>Counselor</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($pending_requests, 0, 5) as $appointment): ?>
                                        <tr>
                                            <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                            <td><?php echo formatTime($appointment['start_time']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['student_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['counselor_name']); ?></td>
                                            <td>
                                                <a href="appointments.php?action=approve&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                                <a href="appointments.php?action=decline&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-danger">Decline</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <a href="appointments.php?status=pending" class="btn btn-sm btn-outline-warning">View All Pending Requests</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
