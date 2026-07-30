<?php
require_once '../config/config.php';
require_once '../includes/auth_functions.php';
require_once '../includes/appointment_functions.php';

requireRole('student');

$error = '';
$success = '';

// Get available counselors
$counselors = getAvailableCounselors();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $counselor_id = intval($_POST['counselor_id']);
    $date = sanitizeInput($_POST['date']);
    $start_time = sanitizeInput($_POST['start_time']);
    $end_time = sanitizeInput($_POST['end_time']);
    $concern = sanitizeInput($_POST['concern']);
    
    $result = bookAppointment($_SESSION['user_id'], $counselor_id, $date, $start_time, $end_time, $concern);
    
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - GuideSched</title>
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
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }
        .form-control:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
        }
        .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
        }
        .form-select:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.2rem rgba(17, 153, 142, 0.25);
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
        .time-slot {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        .time-slot:hover {
            border-color: #11998e;
            background: #f0fff4;
        }
        .time-slot.selected {
            border-color: #11998e;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        .time-slot.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f0f0f0;
        }
        .counselor-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .counselor-card:hover {
            border-color: #11998e;
            background: #f0fff4;
        }
        .counselor-card.selected {
            border-color: #11998e;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
        }
        .step::after {
            content: '';
            position: absolute;
            top: 50%;
            right: -50%;
            width: 100%;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }
        .step:last-child::after {
            display: none;
        }
        .step.active .step-number {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 600;
            position: relative;
            z-index: 1;
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
                    <a class="nav-link active" href="book-appointment.php">
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
                <div class="card p-4">
                    <h2 class="mb-4">Book an Appointment</h2>
                    
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
                        <div class="mt-3">
                            <a href="appointments.php" class="btn btn-primary">View My Appointments</a>
                            <a href="book-appointment.php" class="btn btn-outline-primary">Book Another Appointment</a>
                        </div>
                    <?php else: ?>
                        <div class="step-indicator">
                            <div class="step active" id="step1-indicator">
                                <div class="step-number">1</div>
                                <small>Select Counselor</small>
                            </div>
                            <div class="step" id="step2-indicator">
                                <div class="step-number">2</div>
                                <small>Choose Date</small>
                            </div>
                            <div class="step" id="step3-indicator">
                                <div class="step-number">3</div>
                                <small>Select Time</small>
                            </div>
                            <div class="step" id="step4-indicator">
                                <div class="step-number">4</div>
                                <small>Complete</small>
                            </div>
                        </div>
                        
                        <form method="POST" action="" id="bookingForm">
                            <!-- Step 1: Select Counselor -->
                            <div id="step1" class="booking-step">
                                <h4 class="mb-3">Step 1: Select a Counselor</h4>
                                <?php if (empty($counselors)): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No counselors available at the moment. Please try again later.
                                    </div>
                                <?php else: ?>
                                    <div class="row">
                                        <?php foreach ($counselors as $counselor): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="counselor-card" onclick="selectCounselor(<?php echo $counselor['id']; ?>, this)">
                                                <h5><?php echo htmlspecialchars($counselor['name']); ?></h5>
                                                <p class="mb-0"><small>Specialization: <?php echo htmlspecialchars($counselor['specialization']); ?></small></p>
                                                <input type="hidden" name="counselor_id" value="<?php echo $counselor['id']; ?>">
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Step 2: Select Date -->
                            <div id="step2" class="booking-step" style="display: none;">
                                <h4 class="mb-3">Step 2: Choose a Date</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="date" class="form-label">Select Date</label>
                                        <input type="date" class="form-control" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="goToStep(1)">Back</button>
                                    <button type="button" class="btn btn-book" onclick="goToStep(3)">Next</button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Select Time -->
                            <div id="step3" class="booking-step" style="display: none;">
                                <h4 class="mb-3">Step 3: Select a Time Slot</h4>
                                <div id="timeSlots" class="row">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Please select a date first to see available time slots.
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="goToStep(2)">Back</button>
                                    <button type="button" class="btn btn-book" onclick="goToStep(4)" id="toStep4Btn" disabled>Next</button>
                                </div>
                            </div>
                            
                            <!-- Step 4: Complete Booking -->
                            <div id="step4" class="booking-step" style="display: none;">
                                <h4 class="mb-3">Step 4: Complete Your Booking</h4>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="concern" class="form-label">What would you like to discuss?</label>
                                        <textarea class="form-control" id="concern" name="concern" rows="4" required placeholder="Please describe the reason for your appointment..."></textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="start_time" id="start_time">
                                <input type="hidden" name="end_time" id="end_time">
                                <div class="mb-3">
                                    <div class="card bg-light p-3">
                                        <h6>Booking Summary:</h6>
                                        <p><strong>Counselor:</strong> <span id="summaryCounselor">-</span></p>
                                        <p><strong>Date:</strong> <span id="summaryDate">-</span></p>
                                        <p><strong>Time:</strong> <span id="summaryTime">-</span></p>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">Back</button>
                                    <button type="submit" class="btn btn-book">Confirm Booking</button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedCounselor = null;
        let selectedTimeSlot = null;
        
        function selectCounselor(counselorId, element) {
            selectedCounselor = counselorId;
            
            // Remove selected class from all cards
            document.querySelectorAll('.counselor-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            element.classList.add('selected');
            
            // Update hidden input
            document.querySelector('input[name="counselor_id"]').value = counselorId;
            
            // Move to next step
            goToStep(2);
        }
        
        function goToStep(step) {
            // Hide all steps
            document.querySelectorAll('.booking-step').forEach(s => s.style.display = 'none');
            
            // Show current step
            document.getElementById('step' + step).style.display = 'block';
            
            // Update indicators
            for (let i = 1; i <= 4; i++) {
                const indicator = document.getElementById('step' + i + '-indicator');
                indicator.classList.remove('active', 'completed');
                if (i < step) {
                    indicator.classList.add('completed');
                } else if (i === step) {
                    indicator.classList.add('active');
                }
            }
            
            // Load time slots when reaching step 3
            if (step === 3) {
                loadTimeSlots();
            }
            
            // Update summary when reaching step 4
            if (step === 4) {
                updateSummary();
            }
        }
        
        function loadTimeSlots() {
            const counselorId = document.querySelector('input[name="counselor_id"]').value;
            const date = document.getElementById('date').value;
            
            if (!counselorId || !date) {
                document.getElementById('timeSlots').innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Please select a counselor and date first.
                        </div>
                    </div>
                `;
                return;
            }
            
            // Simulate loading (in real app, this would be an AJAX call)
            document.getElementById('timeSlots').innerHTML = `
                <div class="col-12">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            `;
            
            // Simulated time slots (in real app, fetch from server)
            setTimeout(() => {
                const slots = [
                    { start: '09:00', end: '10:00' },
                    { start: '10:00', end: '11:00' },
                    { start: '11:00', end: '12:00' },
                    { start: '13:00', end: '14:00' },
                    { start: '14:00', end: '15:00' },
                    { start: '15:00', end: '16:00' },
                    { start: '16:00', end: '17:00' }
                ];
                
                let slotsHTML = '';
                slots.forEach(slot => {
                    slotsHTML += `
                        <div class="col-md-4 mb-3">
                            <div class="time-slot" onclick="selectTimeSlot('${slot.start}', '${slot.end}', this)">
                                <strong>${slot.start} - ${slot.end}</strong>
                            </div>
                        </div>
                    `;
                });
                
                document.getElementById('timeSlots').innerHTML = slotsHTML;
            }, 500);
        }
        
        function selectTimeSlot(start, end, element) {
            selectedTimeSlot = { start, end };
            
            // Remove selected class from all slots
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });
            
            // Add selected class to clicked slot
            element.classList.add('selected');
            
            // Update hidden inputs
            document.getElementById('start_time').value = start;
            document.getElementById('end_time').value = end;
            
            // Enable next button
            document.getElementById('toStep4Btn').disabled = false;
        }
        
        function updateSummary() {
            const counselorId = document.querySelector('input[name="counselor_id"]').value;
            const date = document.getElementById('date').value;
            
            // Get counselor name
            const counselorCard = document.querySelector('.counselor-card.selected');
            if (counselorCard) {
                document.getElementById('summaryCounselor').textContent = counselorCard.querySelector('h5').textContent;
            }
            
            document.getElementById('summaryDate').textContent = date ? new Date(date).toLocaleDateString() : '-';
            document.getElementById('summaryTime').textContent = selectedTimeSlot ? `${selectedTimeSlot.start} - ${selectedTimeSlot.end}` : '-';
        }
    </script>
</body>
</html>
