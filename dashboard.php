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
    <!-- MediaPipe Hands CDN scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>
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
                    <div class="camera-overlay d-flex flex-column gap-2">
                        <span class="badge bg-success p-2 text-uppercase" id="feed-badge">
                            <i class="fa-solid fa-video me-1"></i> Live feed
                        </span>
                        <span class="badge bg-secondary p-2" id="detected-gesture-text" style="font-size: 11px; text-transform: uppercase;">
                            Detected: None
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
                        <a href="contacts.php" class="action-card">
                            <div class="action-icon"><i class="fa-solid fa-address-book"></i></div>
                            <h5 class="fw-bold mb-1">Emergency Contacts</h5>
                            <p class="text-muted small mb-0">Add/remove trusted safety contacts</p>
                        </a>
                    </div>
                    
                    <div class="col-12 col-md-6 col-lg-12">
                        <a href="alert_history.php" class="action-card">
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
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved. | <a href="admin_login.php" style="color: rgba(0,0,0,0.6); text-decoration: none;">Admin Porta    <!-- JavaScript to request webcam, load MediaPipe, and handle gesture recognition -->
    <script>
        let isEmergencyTriggered = false;
        let gestureHoldStartTime = null;
        const REQUIRED_HOLD_TIME_MS = 3000;
        const targetGesture = '<?php echo h($current_gesture); ?>';

        function classifyGesture(landmarks) {
            // Check if fingers are up (y is inverted in MediaPipe, smaller y means higher in coordinate space)
            const indexUp = landmarks[8].y < landmarks[6].y;
            const middleUp = landmarks[12].y < landmarks[10].y;
            const ringUp = landmarks[16].y < landmarks[14].y;
            const pinkyUp = landmarks[20].y < landmarks[18].y;
            
            // For thumb: extended when TIP is higher (smaller y) than IP & MCP
            const thumbUp = landmarks[4].y < landmarks[3].y && landmarks[3].y < landmarks[2].y;
            
            // Count standard fingers up (excluding thumb)
            const fingersUpCount = (indexUp ? 1 : 0) + (middleUp ? 1 : 0) + (ringUp ? 1 : 0) + (pinkyUp ? 1 : 0);
            
            // 1. Fist: All fingers are folded down, thumb not up
            if (fingersUpCount === 0 && !thumbUp) {
                return "Fist";
            }
            
            // 2. Thumbs Up: Thumb is up, all other fingers closed
            if (thumbUp && fingersUpCount === 0) {
                return "Thumbs Up";
            }
            
            // 3. One Finger: Index finger up, others folded down
            if (indexUp && fingersUpCount === 1 && !thumbUp) {
                return "One Finger";
            }
            
            // 4. Two Fingers: Index & Middle fingers up, others folded down
            if (indexUp && middleUp && fingersUpCount === 2 && !thumbUp) {
                return "Two Fingers";
            }
            
            return "Unknown";
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const webcam = document.getElementById('webcam');
            const viewport = document.getElementById('camera-viewport');
            
            // Attempt to access user camera
            <?php if ($_SESSION['camera_enabled']): ?>
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                webcam.srcObject = stream;
                webcam.classList.remove('d-none');
                viewport.classList.add('d-none');
                
                // If MediaPipe is loaded, start tracking
                if (typeof Hands !== 'undefined') {
                    webcam.onloadeddata = () => {
                        const hands = new Hands({
                            locateFile: (file) => {
                                return `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`;
                            }
                        });

                        hands.setOptions({
                            maxNumHands: 1,
                            modelComplexity: 1,
                            minDetectionConfidence: 0.6,
                            minTrackingConfidence: 0.6
                        });

                        hands.onResults(onHandResults);

                        async function sendFrame() {
                            if (webcam && !webcam.paused && webcam.srcObject && !isEmergencyTriggered) {
                                try {
                                    await hands.send({ image: webcam });
                                } catch (e) {
                                    console.error("MediaPipe Hands processing error:", e);
                                }
                            }
                            requestAnimationFrame(sendFrame);
                        }
                        sendFrame();
                    };
                } else {
                    console.warn("MediaPipe Hands library failed to load or is offline. Operating in simulation-only mode.");
                    document.getElementById('detected-gesture-text').textContent = "Offline Mode (Simulation Active)";
                }
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

        function onHandResults(results) {
            const badge = document.getElementById('system-status');
            const feedBadge = document.getElementById('feed-badge');
            
            if (isEmergencyTriggered) return;

            let detectedGesture = "None";

            if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
                const landmarks = results.multiHandLandmarks[0];
                detectedGesture = classifyGesture(landmarks);
                document.getElementById('detected-gesture-text').textContent = "Detected: " + detectedGesture;
            } else {
                document.getElementById('detected-gesture-text').textContent = "Detected: None";
            }

            // Verify if the active gesture matches the configured target gesture
            if (detectedGesture === targetGesture) {
                if (gestureHoldStartTime === null) {
                    gestureHoldStartTime = Date.now();
                    badge.className = 'status-badge status-active bg-warning text-dark border-warning animate-pulse';
                    badge.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span>Validating gesture (hold 3s)...</span>
                    `;
                } else {
                    const elapsed = Date.now() - gestureHoldStartTime;
                    if (elapsed >= REQUIRED_HOLD_TIME_MS) {
                        gestureHoldStartTime = null;
                        triggerRealEmergency("Webcam Gesture");
                    }
                }
            } else {
                // If user drops the gesture or shows a wrong gesture, reset hold validation
                if (gestureHoldStartTime !== null) {
                    gestureHoldStartTime = null;
                    badge.className = 'status-badge status-active';
                    badge.innerHTML = `
                        <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>
                        <span>Monitoring Active</span>
                    `;
                }
            }
        }

        function triggerRealEmergency(triggerType = "Manual Simulation") {
            isEmergencyTriggered = true;
            const badge = document.getElementById('system-status');
            const feedBadge = document.getElementById('feed-badge');
            const cancelBtn = document.getElementById('btn-cancel-demo');
            
            fetch('trigger_emergency.php', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        badge.className = 'status-badge status-emergency';
                        badge.innerHTML = `
                            <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>
                            <span>🔴 Emergency Active</span>
                        `;
                        
                        feedBadge.className = 'badge bg-danger p-2 text-uppercase';
                        feedBadge.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1 animate-flash"></i> Alert Sent';
                        
                        cancelBtn.classList.remove('d-none');
                        
                        alert(`SILENT EMERGENCY ALERT SENT!\nTrigger Method: ${triggerType}\nCorrect gesture '${data.log.gesture}' detected and held for 3 seconds!\nEmergency logged at: ${data.log.location}`);
                    } else {
                        isEmergencyTriggered = false;
                        alert("Error logging emergency: " + data.message);
                    }
                })
                .catch(err => {
                    isEmergencyTriggered = false;
                    console.error("AJAX error logging emergency: ", err);
                    alert("Network error: Could not record emergency log.");
                });
        }

        function triggerMockEmergency() {
            // Bypass camera ML and trigger emergency instantly (used for mock testing/offline)
            triggerRealEmergency("Mock Trigger Button");
        }

        function cancelMockEmergency() {
            const badge = document.getElementById('system-status');
            const feedBadge = document.getElementById('feed-badge');
            const cancelBtn = document.getElementById('btn-cancel-demo');
            
            fetch('cancel_emergency.php', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        isEmergencyTriggered = false;
                        gestureHoldStartTime = null;
                        
                        badge.className = 'status-badge status-active';
                        badge.innerHTML = `
                            <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>
                            <span>Monitoring Active</span>
                        `;
                        
                        feedBadge.className = 'badge bg-success p-2 text-uppercase';
                        feedBadge.innerHTML = '<i class="fa-solid fa-video me-1"></i> Live feed';
                        
                        cancelBtn.classList.add('d-none');
                    } else {
                        alert("Error resetting emergency: " + data.message);
                    }
                })
                .catch(err => {
                    console.error("AJAX error resetting emergency: ", err);
                    alert("Network error: Could not cancel active emergency.");
                });
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
