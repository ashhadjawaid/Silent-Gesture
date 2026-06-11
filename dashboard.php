<?php
/**
 * Main Dashboard
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Force authentication
require_auth();

$user_id = $_SESSION['user_id'];

// Get user gesture
$current_gesture = get_user_gesture($user_id);
if (empty($current_gesture)) {
    // Redirect to gesture setup if not configured yet
    header("Location: gesture_setup.php");
    exit;
}

// Map gesture to emoji
$gesture_emojis = [
    'One Finger' => '☝️',
    'Two Fingers' => '✌️',
    'Thumbs Up' => '👍',
    'Fist' => '✊'
];
$gesture_emoji = $gesture_emojis[$current_gesture] ?? '✌️';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Silent Gesture Emergency System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f3f4f6; /* Dashboard main area has light background with cards */
            color: var(--text-dark);
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .status-badge {
            font-size: 14px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
        }

        .status-active {
            background-color: rgba(22, 163, 74, 0.1);
            color: var(--success-green);
            border: 1px solid var(--success-green);
        }

        .status-emergency {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--emergency-red);
            border: 1px solid var(--emergency-red);
            animation: pulse-danger 1.5s infinite;
        }

        @keyframes pulse-danger {
            0% { opacity: 0.8; }
            50% { opacity: 1; transform: scale(1.02); }
            100% { opacity: 0.8; }
        }

        /* Camera Frame design */
        .camera-container {
            background-color: #0A192F;
            border-radius: 16px;
            overflow: hidden;
            border: 3px solid #0d1e3d;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            position: relative;
            aspect-ratio: 16/9;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .camera-overlay {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 10;
        }

        .gesture-reminder {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: rgba(10, 25, 47, 0.85);
            color: #ffffff;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
            z-index: 10;
        }

        .camera-feed {
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.8;
            background-color: #000;
        }

        .action-card {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            padding: 24px;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
            border-color: var(--primary-blue);
        }

        .action-icon {
            font-size: 32px;
            color: var(--primary-blue);
            margin-bottom: 12px;
        }

        .camera-placeholder {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
        }
        
        .camera-placeholder i {
            font-size: 64px;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.4);
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
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <li class="nav-item ms-lg-3 text-white-50">Welcome, <strong><?php echo h($_SESSION['user_name']); ?></strong></li>
                    <li class="nav-item ms-3"><a class="btn btn-danger btn-sm py-1 px-3" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Dashboard Body -->
    <div class="dashboard-container">
        
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 fw-bold text-dark">Silent Monitoring Console</h1>
                <p class="text-muted">The emergency safety system is running. Hold your configured gesture for 3 seconds to alert contacts.</p>
            </div>
            <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end">
                <div class="status-badge status-active" id="system-status">
                    <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>
                    <span>Monitoring Active</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Camera Section (Column 8) -->
            <div class="col-lg-8">
                <div class="camera-container">
                    
                    <!-- Overlay Status -->
                    <div class="camera-overlay">
                        <span class="badge bg-success p-2 text-uppercase" id="feed-badge">
                            <i class="fa-solid fa-video me-1"></i> Live feed
                        </span>
                    </div>

                    <!-- Gesture Reminder -->
                    <div class="gesture-reminder">
                        <span>SOS Gesture: <strong><?php echo $gesture_emoji . ' ' . h($current_gesture); ?></strong></span>
                    </div>

                    <!-- Camera Placeholder/Mock View -->
                    <div class="camera-placeholder" id="camera-viewport">
                        <i class="fa-solid fa-video-slash"></i>
                        <h5 class="fw-semibold">Camera Access Requesting...</h5>
                        <p class="mb-0 text-white-50 px-4">Webcam access is active for gesture detection</p>
                    </div>

                    <video class="camera-feed d-none" id="webcam" autoplay playsinline></video>
                </div>
                
                <!-- Quick Test Actions for prototype demo -->
                <div class="card mt-3 border-0 shadow-sm rounded-3">
                    <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center bg-white">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning text-dark me-2">Demo mode</span>
                            <small class="text-muted">Simulate correct gesture to test emergency activation:</small>
                        </div>
                        <div>
                            <button class="btn btn-outline-danger btn-sm px-3" onclick="triggerMockEmergency()">Simulate Gesture</button>
                            <button class="btn btn-outline-secondary btn-sm px-3 ms-2 d-none" id="btn-cancel-demo" onclick="cancelMockEmergency()">Reset</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Cards Actions (Column 4) -->
            <div class="col-lg-4">
                <div class="row g-3 h-100">
                    <div class="col-12 col-md-6 col-lg-12">
                        <a href="#" class="action-card" onclick="alert('Emergency Contacts Page is assigned to another team member.')">
                            <div class="action-icon"><i class="fa-solid fa-address-book"></i></div>
                            <h5 class="fw-bold mb-1">Emergency Contacts</h5>
                            <p class="text-muted small mb-0">Add/remove trusted safety contacts</p>
                        </a>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-12">
                        <a href="#" class="action-card" onclick="alert('Alert History Page is assigned to another team member.')">
                            <div class="action-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                            <h5 class="fw-bold mb-1">Alert History</h5>
                            <p class="text-muted small mb-0">Review previous alert timestamps & logs</p>
                        </a>
                    </div>

                    <div class="col-12 col-md-6 col-lg-12">
                        <a href="settings.php" class="action-card">
                            <div class="action-icon" style="color: #0A192F;"><i class="fa-solid fa-gears"></i></div>
                            <h5 class="fw-bold mb-1">System Settings</h5>
                            <p class="text-muted small mb-0">Configure gesture, camera, & alerts</p>
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Footer -->
    <div class="footer-text" style="color: rgba(0,0,0,0.5);">
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved.</p>
    </div>

    <!-- JavaScript to request webcam and handle mock simulation -->
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const webcam = document.getElementById('webcam');
            const viewport = document.getElementById('camera-viewport');
            
            // Attempt to access user camera to prove camera permission validation works!
            <?php if ($_SESSION['camera_enabled']): ?>
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                webcam.srcObject = stream;
                webcam.classList.remove('d-none');
                viewport.classList.add('d-none');
            } catch (err) {
                console.error("Camera access failed: ", err);
                let messageTitle = "Camera Access Blocked";
                let messageDesc = "Please enable camera permission in your browser or settings.";
                let iconClass = "fa-solid fa-triangle-exclamation text-warning";
                
                if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    messageTitle = "No Camera Detected";
                    messageDesc = "Please connect a physical webcam to use gesture recognition.";
                    iconClass = "fa-solid fa-camera text-danger";
                }
                
                viewport.innerHTML = `
                    <i class="${iconClass}"></i>
                    <h5 class="fw-semibold mt-2">${messageTitle}</h5>
                    <p class="mb-0 text-white-50 px-4">${messageDesc}</p>
                `;
            }
            <?php else: ?>
            viewport.innerHTML = `
                <i class="fa-solid fa-video-slash text-muted"></i>
                <h5 class="fw-semibold">Camera is Deactivated</h5>
                <p class="mb-0 text-white-50 px-4">Enable camera activation in System Settings</p>
            `;
            <?php endif; ?>
        });

        function triggerMockEmergency() {
            const badge = document.getElementById('system-status');
            const feedBadge = document.getElementById('feed-badge');
            const cancelBtn = document.getElementById('btn-cancel-demo');
            
            // Toggle active status UI
            badge.className = 'status-badge status-emergency';
            badge.innerHTML = `
                <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>
                <span>🔴 Emergency Active</span>
            `;
            
            feedBadge.className = 'badge bg-danger p-2 text-uppercase';
            feedBadge.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1 animate-flash"></i> Alert Sent';
            
            cancelBtn.classList.remove('d-none');
            alert("Correct Gesture Detected and Validated for 3 Seconds! Emergency Mode activated silently.");
        }

        function cancelMockEmergency() {
            const badge = document.getElementById('system-status');
            const feedBadge = document.getElementById('feed-badge');
            const cancelBtn = document.getElementById('btn-cancel-demo');
            
            // Restore active status UI
            badge.className = 'status-badge status-active';
            badge.innerHTML = `
                <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>
                <span>Monitoring Active</span>
            `;
            
            feedBadge.className = 'badge bg-success p-2 text-uppercase';
            feedBadge.innerHTML = '<i class="fa-solid fa-video me-1"></i> Live feed';
            
            cancelBtn.classList.add('d-none');
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
