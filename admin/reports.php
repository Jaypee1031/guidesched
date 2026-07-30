<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/admin_functions.php';

requireRole('admin');

// Get filters
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$counselor_filter = isset($_GET['counselor']) && is_numeric($_GET['counselor']) ? intval($_GET['counselor']) : null;
$date_from = isset($_GET['date_from']) ? sanitizeInput($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeInput($_GET['date_to']) : '';

// Get appointments for report
$appointments = getAllAppointments($status_filter, $counselor_filter, $date_from, $date_to);

// Get counselors for filter
$counselors = getAvailableCounselors();

// Get statistics
$stats = getAdminStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - GuideSched</title>
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
        .report-section {
            background: #f0fff4;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn-export {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            color: white;
        }
        .btn-export:hover {
            color: white;
            transform: translateY(-2px);
        }
        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #11998e;
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
                    <a class="nav-link" href="counselors.php">
                        <i class="fas fa-user-tie"></i> Counselors
                    </a>
                    <a class="nav-link" href="analytics.php">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                    <a class="nav-link active" href="reports.php">
                        <i class="fas fa-file-alt"></i> Reports
                    </a>
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <h2 class="mb-4">Reports</h2>
                
                <!-- Report Filter -->
                <div class="report-section">
                    <h4 class="mb-3">Generate Report</h4>
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
                                <button type="submit" class="btn btn-primary w-100">Generate</button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Report Summary -->
                <div class="card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>Appointment Report</h4>
                        <div>
                            <button class="btn btn-export" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-2"></i>Export Excel
                            </button>
                            <button class="btn btn-export" onclick="window.print()">
                                <i class="fas fa-file-pdf me-2"></i>Print/PDF
                            </button>
                        </div>
                    </div>
                    
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h5>Total Appointments</h5>
                                <h3><?php echo count($appointments); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h5>Completed</h5>
                                <h3 class="text-success"><?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'completed')); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h5>Pending</h5>
                                <h3 class="text-warning"><?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'pending')); ?></h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="summary-card">
                                <h5>Cancelled</h5>
                                <h3 class="text-danger"><?php echo count(array_filter($appointments, fn($a) => $a['status'] === 'cancelled')); ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Report Table -->
                    <?php if (empty($appointments)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No appointments found matching the selected criteria.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive" id="reportTable">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Student</th>
                                        <th>Counselor</th>
                                        <th>Concern</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo formatDate($appointment['appointment_date']); ?></td>
                                        <td><?php echo formatTime($appointment['start_time']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['counselor_name']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($appointment['concern'], 0, 50)) . '...'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $appointment['status'] === 'completed' ? 'success' : ($appointment['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <p class="text-muted">Report generated on <?php echo date('F j, Y, g:i A'); ?></p>
                            <p class="text-muted">Showing <?php echo count($appointments); ?> appointment(s)</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exportToExcel() {
            let csv = [];
            const rows = document.querySelectorAll("#reportTable tr");
            
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll("td, th");
                
                for (let j = 0; j < cols.length; j++) {
                    row.push(cols[j].innerText.replace(/,/g, ""));
                }
                
                csv.push(row.join(","));
            }
            
            const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
            const downloadLink = document.createElement("a");
            downloadLink.download = "appointments_report.csv";
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
    </script>
</body>
</html>
