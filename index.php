<?php
/**
 * Home Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silent Gesture Emergency Safety System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .hero-section {
            padding: 100px 0;
            background: radial-gradient(circle at 80% 20%, rgba(11, 94, 215, 0.15) 0%, transparent 50%), 
                        linear-gradient(135deg, #0A192F 0%, #050d1a 100%);
            color: #ffffff;
            border-bottom: 3px solid rgba(255, 255, 255, 0.05);
        }
        
        .hero-title {
            font-size: 48px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 20px;
        }

        .hero-description {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 35px;
            font-weight: 300;
        }

        .feature-card {
            background-color: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 30px;
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            background-color: rgba(255, 255, 255, 0.06);
            border-color: rgba(11, 94, 215, 0.4);
        }

        .feature-icon {
            font-size: 28px;
            color: #3b82f6;
            margin-bottom: 15px;
        }

        .feature-title {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .feature-desc {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 0;
        }

        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: #ffffff;
            text-align: center;
            margin-bottom: 50px;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <span class="fs-4 fw-bold text-uppercase">🛡️ Silent Gesture System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <?php if (is_authenticated()): ?>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary py-1 px-3" href="dashboard.php">Dashboard</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-outline-custom py-1 px-3" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <h1 class="hero-title">Silent Gesture Emergency System</h1>
                    <p class="hero-description">Smart emergency safety support using real-time gesture recognition. Designed for users who may be in danger and cannot make calls or speak out loud for help.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <?php if (is_authenticated()): ?>
                            <a href="dashboard.php" class="btn btn-primary btn-lg px-4">Open Dashboard</a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary btn-lg px-4">Get Started</a>
                            <a href="#features" class="btn btn-outline-custom btn-lg px-4">Learn More</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5 text-center">
                    <div class="p-4 rounded-4" style="background-color: rgba(255, 255, 255, 0.02); border: 2px solid rgba(255,255,255,0.05); display: inline-block;">
                        <span style="font-size: 150px; line-height: 1;">✌️</span>
                        <div class="mt-3 text-white-50 small">Show gesture to activate emergency mode</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5 my-5" id="features">
        <h2 class="section-title">Main Features</h2>
        <div class="row g-4">
            
            <!-- Feature 1 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-hand-pointer"></i></div>
                    <h4 class="feature-title">Gesture Recognition</h4>
                    <p class="feature-desc">Detect hand movements and identify selected gestures using MediaPipe Hands algorithm.</p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-wifi-slash"></i></div>
                    <h4 class="feature-title">Offline Support</h4>
                    <p class="feature-desc">The application operates fully offline. Camera monitoring and log updates function during network downtime.</p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-video"></i></div>
                    <h4 class="feature-title">Camera Monitoring</h4>
                    <p class="feature-desc">Continuous automatic scan keeps track of hand positions inside a styled camera frame.</p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <h4 class="feature-title">Secure Access</h4>
                    <p class="feature-desc">Encrypted databases and tokenized remembered login sessions keep your configuration private.</p>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-bell"></i></div>
                    <h4 class="feature-title">Emergency Alerts</h4>
                    <p class="feature-desc">Triggers notifications, emails, and logs alerts to the dashboard automatically when online.</p>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-clock"></i></div>
                    <h4 class="feature-title">3-Second Hold</h4>
                    <p class="feature-desc">Requires holding the gesture for 3 seconds, eliminating accidental false activations.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- About Section -->
    <div class="container py-5 my-5" id="about" style="border-top: 1px solid rgba(255, 255, 255, 0.05);">
        <div class="row align-items-center g-5 text-white">
            <div class="col-lg-6">
                <h3 class="fw-bold mb-4">About the Safety System</h3>
                <p class="text-white-50">This system has been built to offer users a quick, discreet, and reliable way of raising an alarm during critical security events. The integration of local sessions, customized setup, and gesture validation ensures a robust defense interface.</p>
                <p class="text-white-50">As the Backend & Authentication Developer (Manal), we focus on ensuring that user profiles are encrypted, login state persists securely across sessions, and emergency gesture inputs sync dynamically with the system configuration.</p>
            </div>
            <div class="col-lg-6 text-center">
                <div class="p-4 rounded-4" style="background-color: rgba(255, 255, 255, 0.02); border: 2px solid rgba(255,255,255,0.05); text-align: left;">
                    <h5 class="fw-bold"><i class="fa-solid fa-server me-2 text-primary"></i>Backend System Info</h5>
                    <hr style="border-color: rgba(255, 255, 255, 0.1);">
                    <ul class="list-unstyled mb-0 text-white-50 lh-lg">
                        <li><i class="fa-solid fa-check text-success me-2"></i>Secure Hash: <code>PASSWORD_BCRYPT</code></li>
                        <li><i class="fa-solid fa-check text-success me-2"></i>Remember Me: 256-bit Token Exchange</li>
                        <li><i class="fa-solid fa-check text-success me-2"></i>Database Engine: MySQL (PDO Layer)</li>
                        <li><i class="fa-solid fa-check text-success me-2"></i>Fallback Mode: Portable SQLite Engine</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-text">
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved. | <a href="admin_login.php">Admin Portal</a></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
