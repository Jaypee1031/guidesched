<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/admin_functions.php';

requireRole('admin');

// Get statistics
$stats = getAdminStatistics();

// Get appointments for analysis
$all_appointments = getAllAppointments();

// Calculate additional analytics
$monthly_data = [];
$status_breakdown = [
    'pending' => 0,
    'approved' => 0,
    'declined' => 0,
    'completed' => 0,
    'cancelled' => 0,
    'no_show' => 0
];

foreach ($all_appointments as $appointment) {
    // Status breakdown
    if (isset($status_breakdown[$appointment['status']])) {
        $status_breakdown[$appointment['status']]++;
    }
    
    // Monthly data
    $month = date('Y-m', strtotime($appointment['appointment_date']));
    if (!isset($monthly_data[$month])) {
        $monthly_data[$month] = 0;
    }
    $monthly_data[$month]++;
}

// Calculate no-show percentage
$total_completed = $stats['completed_sessions'] + $stats['no_shows'];
$no_show_percentage = $total_completed > 0 ? round(($stats['no_shows'] / $total_completed) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - GuideSched</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        .stat-card p {
            margin: 0;
            opacity: 0.9;
        }
        .chart-container {
            position: relative;
            height: 300px;
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
                    <a class="nav-link active" href="analytics.php">
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
                <h2 class="mb-4">Analytics & Statistics</h2>
                
                <!-- Key Metrics -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h3><?php echo $no_show_percentage; ?>%</h3>
                            <p>No-Show Rate</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <h3><?php echo $stats['total_appointments']; ?></h3>
                            <p>Total Appointments</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <h3><?php echo $stats['completed_sessions']; ?></h3>
                            <p>Completed Sessions</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #1fa463 0%, #4ade80 100%);">
                            <h3><?php echo $stats['total_students']; ?></h3>
                            <p>Active Students</p>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card p-4">
                            <h4 class="mb-4">Appointment Status Breakdown</h4>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-4">
                            <h4 class="mb-4">Monthly Appointments</h4>
                            <div class="chart-container">
                                <canvas id="monthlyChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Statistics -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card p-4">
                            <h4 class="mb-4">Key Performance Indicators</h4>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h5>Approval Rate</h5>
                                        <h3 class="text-success">
                                            <?php 
                                            $total_decisions = $stats['approved_appointments'] + $stats['declined'];
                                            echo $total_decisions > 0 ? round(($stats['approved_appointments'] / $total_decisions) * 100, 1) : 0; 
                                            ?>%
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h5>Cancellation Rate</h5>
                                        <h3 class="text-warning">
                                            <?php 
                                            echo $stats['total_appointments'] > 0 ? round(($stats['cancelled_appointments'] / $stats['total_appointments']) * 100, 1) : 0; 
                                            ?>%
                                        </h3>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h5>Completion Rate</h5>
                                        <h3 class="text-info">
                                            <?php 
                                            echo $stats['total_appointments'] > 0 ? round(($stats['completed_sessions'] / $stats['total_appointments']) * 100, 1) : 0; 
                                            ?>%
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Status Breakdown Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved', 'Declined', 'Completed', 'Cancelled', 'No Show'],
                datasets: [{
                    data: [
                        <?php echo $status_breakdown['pending']; ?>,
                        <?php echo $status_breakdown['approved']; ?>,
                        <?php echo $status_breakdown['declined']; ?>,
                        <?php echo $status_breakdown['completed']; ?>,
                        <?php echo $status_breakdown['cancelled']; ?>,
                        <?php echo $status_breakdown['no_show']; ?>
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#38ef7d',
                        '#dc3545',
                        '#11998e',
                        '#6c757d',
                        '#4ade80'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Monthly Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($monthly_data)); ?>,
                datasets: [{
                    label: 'Appointments',
                    data: <?php echo json_encode(array_values($monthly_data)); ?>,
                    backgroundColor: 'rgba(17, 153, 142, 0.8)',
                    borderColor: 'rgba(17, 153, 142, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
