<?php
/**
 * Gesture Setup Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Force authentication
require_auth();

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Check if user already has a selected gesture
$current_gesture = get_user_gesture($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_gesture = trim($_POST['selected_gesture'] ?? '');
    
    $valid_gestures = ['One Finger', 'Two Fingers', 'Thumbs Up', 'Fist'];
    
    if (empty($selected_gesture)) {
        $errors[] = "Please select an emergency gesture.";
    } elseif (!in_array($selected_gesture, $valid_gestures)) {
        $errors[] = "Invalid gesture selected.";
    }
    
    if (empty($errors)) {
        try {
            // Check if user already has a setting row
            $stmt = $pdo->prepare("SELECT id FROM gesture_settings WHERE user_id = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Update existing gesture
                $stmt = $pdo->prepare("UPDATE gesture_settings SET selected_gesture = ? WHERE user_id = ?");
                $stmt->execute([$selected_gesture, $user_id]);
            } else {
                // Insert new gesture
                $stmt = $pdo->prepare("INSERT INTO gesture_settings (user_id, selected_gesture) VALUES (?, ?)");
                $stmt->execute([$user_id, $selected_gesture]);
            }
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Emergency Gesture - Silent Gesture System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .gesture-card {
            border: 2px solid #E5E7EB;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        .gesture-card:hover {
            border-color: #0B5ED7;
            background-color: rgba(11, 94, 215, 0.02);
            transform: translateY(-2px);
        }
        .gesture-card.selected {
            border-color: #0B5ED7;
            background-color: rgba(11, 94, 215, 0.08);
            box-shadow: 0 4px 12px rgba(11, 94, 215, 0.15);
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
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item text-white-50 me-3">Welcome, <strong><?php echo h($_SESSION['user_name']); ?></strong></li>
                    <li class="nav-item"><a class="btn btn-outline-danger btn-sm py-1 px-3" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="auth-container">
        <div class="security-card" style="max-width: 600px;">
            <div class="security-card-header">
                <h2>Select Your Emergency Gesture</h2>
                <p>Choose one gesture that will be used to activate the silent emergency mode.</p>
            </div>
            <div class="security-card-body">
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo h($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="gesture_setup.php" method="POST" id="gestureForm">
                    <input type="hidden" name="selected_gesture" id="selected_gesture_val" value="<?php echo h($current_gesture ?? ''); ?>">
                    
                    <div class="gesture-grid">
                        <!-- Card 1: One Finger -->
                        <div class="gesture-card <?php echo ($current_gesture === 'One Finger') ? 'selected' : ''; ?>" data-gesture="One Finger">
                            <span class="gesture-emoji">☝️</span>
                            <span class="gesture-name">One Finger</span>
                        </div>
                        
                        <!-- Card 2: Two Fingers -->
                        <div class="gesture-card <?php echo ($current_gesture === 'Two Fingers' || empty($current_gesture)) ? 'selected' : ''; ?>" data-gesture="Two Fingers">
                            <span class="gesture-emoji">✌️</span>
                            <span class="gesture-name">Two Fingers</span>
                            <small class="text-primary d-block mt-1" style="font-size: 11px;">Recommended</small>
                        </div>
                        
                        <!-- Card 3: Thumbs Up -->
                        <div class="gesture-card <?php echo ($current_gesture === 'Thumbs Up') ? 'selected' : ''; ?>" data-gesture="Thumbs Up">
                            <span class="gesture-emoji">👍</span>
                            <span class="gesture-name">Thumbs Up</span>
                        </div>
                        
                        <!-- Card 4: Fist -->
                        <div class="gesture-card <?php echo ($current_gesture === 'Fist') ? 'selected' : ''; ?>" data-gesture="Fist">
                            <span class="gesture-emoji">✊</span>
                            <span class="gesture-name">Fist</span>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-emergency">Save Gesture</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-text">
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved.</p>
    </div>

    <!-- JS for Interactive Card Selection -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.gesture-card');
            const hiddenInput = document.getElementById('selected_gesture_val');

            // Set default value if empty (default to Two Fingers)
            if (!hiddenInput.value) {
                hiddenInput.value = 'Two Fingers';
            }

            cards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selected class from all cards
                    cards.forEach(c => c.classList.remove('selected'));
                    
                    // Add selected class to clicked card
                    this.classList.add('selected');
                    
                    // Update hidden input value
                    hiddenInput.value = this.getAttribute('data-gesture');
                });
            });
        });
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
