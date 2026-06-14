<?php
/**
 * Alert History Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Force authentication
require_auth();

$user_id = $_SESSION['user_id'];
$errors = [];

try {
    // Fetch logs for this user sorted by ID descending (most recent first)
    $stmt = $pdo->prepare("SELECT * FROM emergency_logs WHERE user_id = ? ORDER BY id DESC");
    $stmt->execute([$user_id]);
    $logs = $stmt->fetchAll();
} catch (PDOException $e) {
    $logs = [];
    $errors[] = "Database error: " . $e->getMessage();
}

// Map gesture names to emojis for nice styling
$gesture_emojis = [
    'One Finger' => '☝️',
    'Two Fingers' => '✌️',
    'Thumbs Up' => '👍',
    'Fist' => '✊'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert History - Silent Gesture Emergency System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f3f4f6;
            color: var(--text-dark);
        }

        .history-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 15px;
        }

        .table-card {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .history-table th {
            background-color: #0d1e3d;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            padding: 16px 20px;
            border: none;
        }

        .history-table td {
            padding: 16px 20px;
            vertical-align: middle;
            font-size: 14px;
            color: var(--text-dark);
            border-bottom: 1px solid var(--border-color);
        }

        .history-table tr:last-child td {
            border-bottom: none;
        }

        .status-pill {
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 50px;
            display: inline-block;
        }

        .pill-emergency {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--emergency-red);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .pill-cancelled {
            background-color: rgba(107, 114, 128, 0.1);
            color: var(--text-muted);
            border: 1px solid rgba(107, 114, 128, 0.2);
        }

        .empty-history-view {
            text-align: center;
            padding: 60px 20px;
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px dashed var(--border-color);
            color: var(--text-muted);
        }

        .empty-history-view i {
            font-size: 56px;
            margin-bottom: 15px;
            color: rgba(0, 0, 0, 0.1);
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
                    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <li class="nav-item ms-lg-3 text-white-50">Welcome, <strong><?php echo h($_SESSION['user_name']); ?></strong></li>
                    <li class="nav-item ms-3"><a class="btn btn-danger btn-sm py-1 px-3" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- History Body -->
    <div class="history-container">
        
        <div class="row align-items-center mb-4">
            <div class="col-8">
                <h1 class="h3 fw-bold text-dark mb-1">Alert History</h1>
                <p class="text-muted small mb-0">Logs of all previous emergency alerts activated from your console</p>
            </div>
            <div class="col-4 text-end">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo h($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Table Card -->
        <?php if (empty($logs)): ?>
            <div class="empty-history-view">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <h5 class="fw-semibold text-dark">No Alerts Triggered Yet</h5>
                <p class="mb-0">Your history will show logs of events here once you simulate an emergency gesture.</p>
            </div>
        <?php else: ?>
            <div class="table-card table-responsive shadow-sm">
                <table class="table history-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Gesture Used</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): 
                            $status_class = ($log['status'] === 'Emergency Active') ? 'pill-emergency' : 'pill-cancelled';
                            $status_label = ($log['status'] === 'Emergency Active') ? 'Emergency' : 'Cancelled';
                            $emoji = $gesture_emojis[$log['gesture_used']] ?? '✌️';
                            $formatted_time = date('h:i A', strtotime($log['time']));
                            $formatted_date = date('Y-m-d', strtotime($log['date']));
                        ?>
                            <tr>
                                <td class="fw-medium"><?php echo h($formatted_date); ?></td>
                                <td><?php echo h($formatted_time); ?></td>
                                <td>
                                    <span class="status-pill <?php echo $status_class; ?>">
                                        <?php echo h($status_label); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark p-2 border">
                                        <?php echo $emoji . ' ' . h($log['gesture_used']); ?>
                                    </span>
                                </td>
                                <td class="text-muted">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                    <?php echo h($log['location'] ?? 'Available'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <div class="footer-text" style="color: rgba(0,0,0,0.5); padding-top: 35px;">
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved. | <a href="admin_login.php" style="color: rgba(0,0,0,0.6); text-decoration: none;">Admin Portal</a></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
