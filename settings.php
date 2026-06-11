<?php
/**
 * Settings Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Force authentication
require_auth();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Fetch current gesture
$current_gesture = get_user_gesture($user_id);
if (empty($current_gesture)) {
    // If they haven't set up a gesture, redirect to setup
    header("Location: gesture_setup.php");
    exit;
}

// Map gesture names to emojis for nice styling
$gesture_emojis = [
    'One Finger' => '☝️',
    'Two Fingers' => '✌️',
    'Thumbs Up' => '👍',
    'Fist' => '✊'
];

$current_emoji = $gesture_emojis[$current_gesture] ?? '❓';

// Mock settings for Camera & Monitoring (could be stored in session or locally, let's store in session for prototype persistence!)
if (!isset($_SESSION['camera_enabled'])) {
    $_SESSION['camera_enabled'] = true;
}
if (!isset($_SESSION['hold_validation'])) {
    $_SESSION['hold_validation'] = true; // 3-second hold validation
}
if (!isset($_SESSION['offline_logging'])) {
    $_SESSION['offline_logging'] = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update settings
    $_SESSION['camera_enabled'] = isset($_POST['camera_enabled']);
    $_SESSION['hold_validation'] = isset($_POST['hold_validation']);
    $_SESSION['offline_logging'] = isset($_POST['offline_logging']);
    
    $success = "Settings updated successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Silent Gesture Emergency System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .settings-list-group .list-group-item {
            border: 1px solid var(--border-color);
            padding: 18px 20px;
            background-color: #ffffff;
            transition: background-color 0.2s;
        }
        .settings-list-group .list-group-item:first-child {
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }
        .settings-list-group .list-group-item:last-child {
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .settings-icon {
            font-size: 18px;
            color: #0A192F;
            width: 30px;
        }
        .form-check-input {
            width: 2.5em;
            height: 1.25em;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
                <span class="fs-4 fw-bold text-uppercase">🛡️ Silent Gesture System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="settings.php">Settings</a></li>
                    <li class="nav-item ms-lg-3 text-white-50">Logged in as: <strong><?php echo h($_SESSION['user_name']); ?></strong></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="auth-container">
        <div class="security-card" style="max-width: 550px;">
            <div class="security-card-header">
                <h2>Settings</h2>
                <p>Manage application preferences & security settings</p>
            </div>
            <div class="security-card-body">
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?php echo h($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 1rem;"></button>
                    </div>
                <?php endif; ?>

                <form action="settings.php" method="POST">
                    
                    <!-- Settings List -->
                    <div class="settings-list-group mb-4">
                        
                        <!-- 1. Change Gesture Row -->
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="settings-icon"><i class="fa-solid fa-hand"></i></span>
                                <div>
                                    <h6 class="mb-0 fw-semibold">Emergency Gesture</h6>
                                    <small class="text-muted">Current: <?php echo $current_emoji . ' ' . h($current_gesture); ?></small>
                                </div>
                            </div>
                            <a href="gesture_setup.php" class="btn btn-outline-primary btn-sm py-1 px-3">Change</a>
                        </div>

                        <!-- 2. Camera Toggle Row -->
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="settings-icon"><i class="fa-solid fa-camera"></i></span>
                                <div>
                                    <h6 class="mb-0 fw-semibold">Camera Activation</h6>
                                    <small class="text-muted">Auto-start camera on dashboard</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="camera_enabled" id="camera_enabled" <?php echo $_SESSION['camera_enabled'] ? 'checked' : ''; ?>>
                            </div>
                        </div>

                        <!-- 3. Hold Validation Row -->
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="settings-icon"><i class="fa-solid fa-stopwatch"></i></span>
                                <div>
                                    <h6 class="mb-0 fw-semibold">3-Second Hold Validation</h6>
                                    <small class="text-muted">Prevents false emergency activation</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="hold_validation" id="hold_validation" <?php echo $_SESSION['hold_validation'] ? 'checked' : ''; ?>>
                            </div>
                        </div>

                        <!-- 4. Offline Working Row -->
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <span class="settings-icon"><i class="fa-solid fa-wifi"></i></span>
                                <div>
                                    <h6 class="mb-0 fw-semibold">Offline Emergency Support</h6>
                                    <small class="text-muted">Save emergency logs locally offline</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="offline_logging" id="offline_logging" <?php echo $_SESSION['offline_logging'] ? 'checked' : ''; ?>>
                            </div>
                        </div>

                    </div>

                    <!-- Save Changes Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>

                    <!-- Logout Button -->
                    <div class="d-grid mb-2">
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-text">
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
