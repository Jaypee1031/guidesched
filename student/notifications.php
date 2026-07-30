<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

// Handle marking as read
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $notification_id = intval($_GET['mark_read']);
    markNotificationAsRead($notification_id, $_SESSION['user_id']);
    redirect('notifications.php');
}

// Handle marking all as read
if (isset($_GET['mark_all_read'])) {
    $notifications = getStudentNotifications($_SESSION['user_id'], true);
    foreach ($notifications as $notification) {
        markNotificationAsRead($notification['id'], $_SESSION['user_id']);
    }
    redirect('notifications.php');
}

// Get notifications
$all_notifications = getStudentNotifications($_SESSION['user_id']);
$unread_notifications = getStudentNotifications($_SESSION['user_id'], true);
$unread_count = count($unread_notifications);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - GuideSched</title>
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
        .notification-item {
            border-left: 4px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .notification-item:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .notification-item.unread {
            border-left-color: #11998e;
            background: #f0fff4;
        }
        .notification-item.approved {
            border-left-color: #28a745;
        }
        .notification-item.declined {
            border-left-color: #dc3545;
        }
        .notification-item.rescheduled {
            border-left-color: #fd7e14;
        }
        .notification-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-right: 15px;
        }
        .notification-icon.info {
            background: #17a2b8;
            color: white;
        }
        .notification-icon.approved {
            background: #28a745;
            color: white;
        }
        .notification-icon.declined {
            background: #dc3545;
            color: white;
        }
        .notification-icon.rescheduled {
            background: #fd7e14;
            color: white;
        }
        .notification-icon.reminder {
            background: #11998e;
            color: white;
        }
        .badge-unread {
            background: #dc3545;
            color: white;
        }
        .btn-mark-read {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            color: white;
        }
        .btn-mark-read:hover {
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
                    <a class="nav-link" href="dashboard.php">
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
                    <a class="nav-link active" href="notifications.php">
                        <i class="fas fa-bell"></i> Notifications
                        <?php if ($unread_count > 0): ?>
                            <span class="badge badge-unread ms-2"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
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
                        <h2>Notifications</h2>
                        <?php if ($unread_count > 0): ?>
                            <a href="notifications.php?mark_all_read=1" class="btn btn-mark-read">
                                <i class="fas fa-check-double me-2"></i>Mark All as Read
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (empty($all_notifications)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No notifications to display.
                        </div>
                    <?php else: ?>
                        <div class="notifications-list">
                            <?php foreach ($all_notifications as $notification): ?>
                            <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?> <?php echo $notification['type']; ?>">
                                <div class="d-flex align-items-start">
                                    <div class="notification-icon <?php echo $notification['type']; ?>">
                                        <i class="fas fa-<?php echo $notification['type'] === 'approved' ? 'check' : ($notification['type'] === 'declined' ? 'times' : ($notification['type'] === 'rescheduled' ? 'clock' : 'bell')); ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="mb-1"><?php echo ucfirst($notification['type']); ?></h5>
                                            <small class="text-muted"><?php echo formatDate($notification['created_at'], 'M j, g:i A'); ?></small>
                                        </div>
                                        <p class="mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                                        
                                        <?php if (!$notification['is_read']): ?>
                                        <a href="notifications.php?mark_read=<?php echo $notification['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-check me-1"></i>Mark as Read
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($notification['appointment_id']): ?>
                                        <a href="appointments.php" class="btn btn-sm btn-outline-secondary ms-2">
                                            <i class="fas fa-calendar me-1"></i>View Appointment
                                        </a>
                                        <?php endif; ?>
                                    </div>
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
