<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';

requireRole('student');

// Get user profile
$user = getUserProfile($_SESSION['user_id']);

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $data = [
            'name' => sanitizeInput($_POST['name']),
            'email' => sanitizeInput($_POST['email']),
            'student_number' => sanitizeInput($_POST['student_number']),
            'course' => sanitizeInput($_POST['course']),
            'year_level' => intval($_POST['year_level']),
            'contact_number' => sanitizeInput($_POST['contact_number'])
        ];
        
        $result = updateUserProfile($_SESSION['user_id'], $data);
        
        if ($result['success']) {
            $success = $result['message'];
            // Refresh user data
            $user = getUserProfile($_SESSION['user_id']);
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error = 'New passwords do not match.';
        } else {
            $result = changePassword($_SESSION['user_id'], $current_password, $new_password);
            
            if ($result['success']) {
                $success = $result['message'];
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GuideSched</title>
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
        .profile-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #11998e;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 auto 20px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
        }
        .btn-save {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
        }
        .btn-save:hover {
            color: white;
            transform: translateY(-2px);
        }
        .nav-tabs .nav-link {
            border: none;
            color: #666;
            font-weight: 600;
            padding: 15px 25px;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 10px;
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
                    <a class="nav-link active" href="profile.php">
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
                <div class="profile-header text-center">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                    </div>
                    <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="mb-0">Student ID: <?php echo htmlspecialchars($user['student_number']); ?></p>
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
                
                <div class="card p-4">
                    <ul class="nav nav-tabs mb-4" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab">
                                <i class="fas fa-graduation-cap me-2"></i>Academic Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                <i class="fas fa-lock me-2"></i>Security
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="profileTabsContent">
                        <!-- Personal Information -->
                        <div class="tab-pane fade show active" id="personal" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="update_profile" value="1">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_number" class="form-label">Contact Number</label>
                                        <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-save">Update Personal Information</button>
                            </form>
                        </div>
                        
                        <!-- Academic Information -->
                        <div class="tab-pane fade" id="academic" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="update_profile" value="1">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="student_number" class="form-label">Student Number</label>
                                        <input type="text" class="form-control" id="student_number" name="student_number" value="<?php echo htmlspecialchars($user['student_number']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="course" class="form-label">Course/Program</label>
                                        <input type="text" class="form-control" id="course" name="course" value="<?php echo htmlspecialchars($user['course']); ?>" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="year_level" class="form-label">Year Level</label>
                                        <select class="form-select" id="year_level" name="year_level" required>
                                            <option value="1" <?php echo $user['year_level'] == 1 ? 'selected' : ''; ?>>1st Year</option>
                                            <option value="2" <?php echo $user['year_level'] == 2 ? 'selected' : ''; ?>>2nd Year</option>
                                            <option value="3" <?php echo $user['year_level'] == 3 ? 'selected' : ''; ?>>3rd Year</option>
                                            <option value="4" <?php echo $user['year_level'] == 4 ? 'selected' : ''; ?>>4th Year</option>
                                            <option value="5" <?php echo $user['year_level'] == 5 ? 'selected' : ''; ?>>5th Year</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-save">Update Academic Information</button>
                            </form>
                        </div>
                        
                        <!-- Security -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            <form method="POST" action="">
                                <input type="hidden" name="change_password" value="1">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-save">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
