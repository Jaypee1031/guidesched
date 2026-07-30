<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

// Get user profile
$user = getUserProfile($_SESSION['user_id']);

// Get appointment statistics
$stats = getStudentAppointmentStats($_SESSION['user_id']);

// Get upcoming appointments
$upcoming_appointments = array_filter(getStudentAppointments($_SESSION['user_id']), function($apt) {
    return $apt['status'] === 'approved' && strtotime($apt['appointment_date']) >= strtotime(date('Y-m-d'));
});

// Get recent notifications
$recent_notifications = array_slice(getStudentNotifications($_SESSION['user_id']), 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - GuideSched</title>
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
        .btn-book {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
        }
        .btn-book:hover {
            color: white;
            transform: translateY(-2px);
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
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                    <a class="nav-link" href="book-appointment.php">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </a>
                    <a class="nav-link" href="appointments.php">
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
                <div class="welcome-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                            <p class="text-muted mb-0">Student ID: <?php echo htmlspecialchars($user['student_number']); ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="user-avatar d-inline-flex">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h3><?php echo $stats['upcoming']; ?></h3>
                            <p>Upcoming Appointments</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h3><?php echo $stats['pending']; ?></h3>
                            <p>Pending Requests</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h3><?php echo $stats['completed']; ?></h3>
                            <p>Completed Sessions</p>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card p-4">
                            <h4 class="mb-4">Quick Actions</h4>
                            <div class="d-grid gap-3">
                                <a href="book-appointment.php" class="btn btn-book">
                                    <i class="fas fa-calendar-plus me-2"></i>Book New Appointment
                                </a>
                                <a href="appointments.php" class="btn btn-outline-primary">
                                    <i class="fas fa-calendar-alt me-2"></i>View My Appointments
                                </a>
                                <a href="profile.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-user me-2"></i>Update Profile
                                </a>
                            </div>
                        </div>
                        
                        <?php if (!empty($upcoming_appointments)): ?>
                        <div class="card p-4 mt-4">
                            <h4 class="mb-4">Upcoming Appointments</h4>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Counselor</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($upcoming_appointments, 0, 3) as $appointment): ?>
                                        <tr>
                                            <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                            <td><?php echo formatTime($appointment['start_time']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['counselor_name']); ?></td>
                                            <td>
                                                <span class="badge bg-success"><?php echo ucfirst($appointment['status']); ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                <a href="appointments.php" class="btn btn-sm btn-outline-primary">View All Appointments</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-4">
                            <h4 class="mb-4">Recent Activity</h4>
                            <?php if (empty($recent_notifications)): ?>
                                <p class="text-muted">No recent activity to display.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_notifications as $notification): ?>
                                    <div class="list-group-item px-0">
                                        <small class="text-muted"><?php echo formatDate($notification['created_at'], 'M j, g:i A'); ?></small>
                                        <p class="mb-1"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        <span class="badge bg-<?php echo $notification['type'] === 'approved' ? 'success' : ($notification['type'] === 'declined' ? 'danger' : 'info'); ?>">
                                            <?php echo ucfirst($notification['type']); ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-3">
                                    <a href="notifications.php" class="btn btn-sm btn-outline-primary">View All Notifications</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
