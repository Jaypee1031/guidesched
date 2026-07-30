<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GuideSched - Guidance Counseling Made Easy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .navbar-brand {
            font-weight: 700;
            color: #11998e !important;
            font-size: 1.5rem;
        }
        .hero {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 100px 0;
            color: white;
            text-align: center;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .hero p {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        .btn-hero {
            background: white;
            color: #11998e;
            border: none;
            border-radius: 30px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: transform 0.2s;
        }
        .btn-hero:hover {
            transform: translateY(-3px);
            color: #11998e;
        }
        .features {
            padding: 80px 0;
            background: #f8f9fa;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .feature-icon {
            font-size: 3rem;
            color: #11998e;
            margin-bottom: 20px;
        }
        .feature-card h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        .how-it-works {
            padding: 80px 0;
        }
        .step-card {
            text-align: center;
            padding: 30px;
        }
        .step-number {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 auto 20px;
        }
        .step-card h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .contact {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 60px 0;
            color: white;
            text-align: center;
        }
        .contact h2 {
            font-weight: 600;
            margin-bottom: 20px;
        }
        .btn-contact {
            background: white;
            color: #11998e;
            border: none;
            border-radius: 30px;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-contact:hover {
            color: #11998e;
        }
        footer {
            background: #333;
            color: white;
            padding: 40px 0;
        }
        footer a {
            color: white;
            text-decoration: none;
        }
        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-check me-2"></i>GuideSched
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary text-white px-4 ms-2" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Your Guidance. Your Schedule. Your Well-being.</h1>
            <p>Making guidance counseling more accessible, one appointment at a time.</p>
            <a href="register.php" class="btn btn-hero">Book an Appointment</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Why Choose GuideSched?</h2>
                <p class="text-muted">Simplify your guidance counseling experience</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-clock feature-icon"></i>
                        <h3>Easy Scheduling</h3>
                        <p>Book appointments online at your convenience. No more waiting in line or phone calls.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-bell feature-icon"></i>
                        <h3>Smart Reminders</h3>
                        <p>Get notified about your upcoming appointments so you never miss a session.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="fas fa-user-shield feature-icon"></i>
                        <h3>Secure & Private</h3>
                        <p>Your information is protected with industry-standard security measures.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">How It Works</h2>
                <p class="text-muted">Get started in 3 simple steps</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h3>Login</h3>
                        <p>Sign in with your student credentials to access the portal.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h3>Choose Schedule</h3>
                        <p>Select your preferred counselor, date, and time slot.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3>Get Support</h3>
                        <p>Attend your appointment and receive the guidance you need.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <h2>Need Help?</h2>
            <p class="mb-4">Contact the Guidance Office for assistance</p>
            <a href="mailto:guidance@university.edu" class="btn btn-contact">
                <i class="fas fa-envelope me-2"></i>Contact Guidance Office
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h4>GuideSched</h4>
                    <p>Making guidance counseling more accessible, one appointment at a time.</p>
                </div>
                <div class="col-md-6">
                    <h4>Quick Links</h4>
                    <ul class="list-unstyled">
                        <li><a href="login.php">Student Login</a></li>
                        <li><a href="register.php">Student Registration</a></li>
                        <li><a href="login.php">Admin/Counselor Login</a></li>
                    </ul>
                </div>
            </div>
            <hr class="mt-4 mb-4">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> GuideSched. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
