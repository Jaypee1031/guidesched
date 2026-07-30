<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/admin_functions.php';

requireRole('admin');

$error = '';
$success = '';

// Handle counselor status change
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

// Get all counselors
$counselors = [];
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counselors Management - GuideSched</title>
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
        .btn-add {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            color: white;
        }
        .btn-add:hover {
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
                    <a class="nav-link" href="students.php">
                        <i class="fas fa-users"></i> Students
                    </a>
                    <a class="nav-link active" href="counselors.php">
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Counselors Management</h2>
                        <a href="add-counselor.php" class="btn btn-add">
                            <i class="fas fa-plus me-2"></i>Add Counselor
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
                    
                    <?php if (empty($counselors)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No counselors found. Add your first counselor to get started.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Counselor ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Specialization</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($counselors as $counselor): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($counselor['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($counselor['name']); ?></td>
                                        <td><?php echo htmlspecialchars($counselor['email']); ?></td>
                                        <td><?php echo htmlspecialchars($counselor['specialization']); ?></td>
                                        <td><?php echo htmlspecialchars($counselor['contact_number']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $counselor['status'] === 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                                <?php echo ucfirst($counselor['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($counselor['status'] === 'active'): ?>
                                                <a href="counselors.php?action=deactivate&id=<?php echo $counselor['id']; ?>" 
                                                   class="btn btn-sm btn-warning"
                                                   onclick="return confirm('Are you sure you want to deactivate this counselor?');">
                                                    <i class="fas fa-user-slash"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="counselors.php?action=activate&id=<?php echo $counselor['id']; ?>" 
                                                   class="btn btn-sm btn-success"
                                                   onclick="return confirm('Are you sure you want to activate this counselor?');">
                                                    <i class="fas fa-user-check"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
