<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/admin_functions.php';

requireRole('admin');

// Get search query
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get students
$students = getAllStudents($search);

// Get selected student for history view
$selected_student_id = isset($_GET['student_id']) && is_numeric($_GET['student_id']) ? intval($_GET['student_id']) : null;
$student_history = [];
$selected_student = null;

if ($selected_student_id) {
    $student_history = getStudentAppointmentHistory($selected_student_id);
    foreach ($students as $student) {
        if ($student['id'] == $selected_student_id) {
            $selected_student = $student;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - GuideSched</title>
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
        .badge-active {
            background: #28a745;
        }
        .badge-inactive {
            background: #dc3545;
        }
        .badge-suspended {
            background: #ffc107;
            color: #000;
        }
        .search-section {
            background: #f0fff4;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .student-card {
            border-left: 4px solid #11998e;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .student-card:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .view-btn {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            color: white;
        }
        .view-btn:hover {
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
                    <small class="d-block mt-2 opacity-75">Admin Portal</small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a class="nav-link" href="appointments.php">
                        <i class="fas fa-calendar-alt"></i> Appointments
                    </a>
                    <a class="nav-link" href="schedule.php">
                        <i class="fas fa-clock"></i> Schedule Management
                    </a>
                    <a class="nav-link active" href="students.php">
                        <i class="fas fa-users"></i> Students
                    </a>
                    <a class="nav-link" href="counselors.php">
                        <i class="fas fa-user-tie"></i> Counselors
                    </a>
                    <a class="nav-link" href="analytics.php">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-file-alt"></i> Reports
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="card p-4">
                    <h2 class="mb-4">Student Management</h2>
                    
                    <!-- Search Section -->
                    <div class="search-section">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-10 mb-3">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Search by name, email, or student number..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <?php if (empty($students)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?php if ($search): ?>
                                No students found matching "<?php echo htmlspecialchars($search); ?>".
                            <?php else: ?>
                                No students registered yet.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-<?php echo $selected_student_id ? '6' : '12'; ?>">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Course</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($student['student_number']); ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $student['status']; ?>">
                                                        <?php echo ucfirst($student['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="students.php?student_id=<?php echo $student['id']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                                                       class="btn btn-sm view-btn">
                                                        <i class="fas fa-eye me-1"></i>View
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <p class="text-muted">Showing <?php echo count($students); ?> student(s)</p>
                                </div>
                            </div>
                            
                            <?php if ($selected_student_id && $selected_student): ?>
                            <div class="col-md-6">
                                <div class="card p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h4>Student Profile</h4>
                                        <a href="students.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </div>
                                    
                                    <div class="student-card">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Name:</strong>
                                                <p><?php echo htmlspecialchars($selected_student['name']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Student ID:</strong>
                                                <p><?php echo htmlspecialchars($selected_student['student_number']); ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Email:</strong>
                                                <p><?php echo htmlspecialchars($selected_student['email']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Contact:</strong>
                                                <p><?php echo htmlspecialchars($selected_student['contact_number']); ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Course:</strong>
                                                <p><?php echo htmlspecialchars($selected_student['course']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Year Level:</strong>
                                                <p><?php echo $selected_student['year_level']; ?></p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Status:</strong>
                                                <p>
                                                    <span class="badge badge-<?php echo $selected_student['status']; ?>">
                                                        <?php echo ucfirst($selected_student['status']); ?>
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Registered:</strong>
                                                <p><?php echo formatDate($selected_student['created_at']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <h5 class="mt-4 mb-3">Appointment History</h5>
                                    <?php if (empty($student_history)): ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            No appointment history found.
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Counselor</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($student_history as $appointment): ?>
                                                    <tr>
                                                        <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                                        <td><?php echo formatTime($appointment['start_time']); ?></td>
                                                        <td><?php echo htmlspecialchars($appointment['counselor_name']); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $appointment['status']; ?>">
                                                                <?php echo ucfirst($appointment['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
