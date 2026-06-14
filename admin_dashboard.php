<?php
/**
 * Admin Dashboard
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Disable browser caching to ensure latest JavaScript and HTML are loaded
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Force admin authentication
require_admin_auth();

// Fetch registered users (with selected gesture if configured)
try {
    $users_stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.phone, gs.selected_gesture 
        FROM users u 
        LEFT JOIN gesture_settings gs ON u.id = gs.user_id 
        ORDER BY u.id DESC
    ");
    $registered_users = $users_stmt->fetchAll();
} catch (PDOException $e) {
    $registered_users = [];
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
    <title>Admin Dashboard - Silent Gesture Emergency System</title>
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

        .dashboard-container {
            max-width: 1250px;
            margin: 40px auto;
            padding: 0 15px;
        }

        /* Stat Cards */
        .stat-card {
            border-radius: 16px;
            color: #ffffff;
            padding: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .stat-card-blue {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
        }

        .stat-card-red {
            background: linear-gradient(135deg, #991b1b, #ef4444);
        }

        .stat-card-green {
            background: linear-gradient(135deg, #166534, #22c55e);
        }

        .stat-card-icon {
            position: absolute;
            right: 20px;
            bottom: 15px;
            font-size: 56px;
            opacity: 0.15;
        }

        .stat-card-value {
            font-size: 36px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-card-label {
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        /* Dashboard Tables */
        .panel-card {
            background-color: #ffffff;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .panel-card-header {
            background-color: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-card-header h5 {
            margin: 0;
            font-weight: 700;
            color: #0d1e3d;
            font-size: 17px;
        }

        .panel-card-body {
            padding: 0;
        }

        .admin-table th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 14px 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .admin-table td {
            padding: 14px 20px;
            font-size: 14px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge-active {
            background-color: rgba(220, 38, 38, 0.1);
            color: var(--emergency-red);
            border: 1px solid var(--emergency-red);
            font-weight: 600;
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 50px;
            animation: pulse-alert 1.5s infinite;
        }

        @keyframes pulse-alert {
            0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
            70% { box-shadow: 0 0 0 8px rgba(220, 38, 38, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
        }

        .status-badge-cancelled {
            background-color: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
            font-weight: 600;
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 50px;
        }

        .user-list-card {
            background-color: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-list-card h6 {
            margin: 0 0 3px 0;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-list-card p {
            margin: 0;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Live Alert Notification bar */
        #live-indicator {
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            color: var(--success-green);
            background-color: rgba(22, 163, 74, 0.1);
            padding: 4px 10px;
            border-radius: 50px;
            font-weight: 600;
        }

        .live-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success-green);
            border-radius: 50%;
            margin-right: 6px;
            animation: live-blink 1.2s infinite;
        }

        @keyframes live-blink {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% { opacity: 0.3; }
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom" style="background: linear-gradient(135deg, #0d1e3d, #0A192F);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="admin_dashboard.php">
                <span class="fs-4 fw-bold text-uppercase">🛡️ Silent Gesture Admin Panel</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item text-white-50 me-3">Logged in as Admin: <strong><?php echo h($_SESSION['admin_email']); ?></strong></li>
                    <li class="nav-item"><a class="btn btn-danger btn-sm py-1 px-3" href="admin_logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Admin Dashboard Area -->
    <div class="dashboard-container">
        
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 fw-bold text-dark mb-1">Safety Operations Command</h1>
                <p class="text-muted small mb-0">Overview of all active client terminals and real-time emergency events monitoring.</p>
            </div>
            <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-2 mt-md-0">
                <div id="live-indicator">
                    <span class="live-dot"></span>
                    <span>Live Console Polling Active</span>
                </div>
            </div>
        </div>

        <!-- 3 Stats Cards Rows -->
        <div class="row g-4 mb-4">
            <!-- 1. Total Users Card -->
            <div class="col-md-4">
                <div class="stat-card stat-card-blue">
                    <div class="stat-card-icon"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-card-value" id="stat-total-users">-</div>
                    <div class="stat-card-label">Total Users</div>
                </div>
            </div>

            <!-- 2. Total Alerts Card -->
            <div class="col-md-4">
                <div class="stat-card stat-card-red">
                    <div class="stat-card-icon"><i class="fa-solid fa-bell"></i></div>
                    <div class="stat-card-value" id="stat-total-alerts">-</div>
                    <div class="stat-card-label">Total Alerts Logged</div>
                </div>
            </div>

            <!-- 3. Active Emergencies Card -->
            <div class="col-md-4">
                <div class="stat-card stat-card-green" id="active-emergencies-card">
                    <div class="stat-card-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="stat-card-value" id="stat-active-emergencies">-</div>
                    <div class="stat-card-label">Active Emergencies</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Recent Emergencies Table (col-lg-8) -->
            <div class="col-lg-8">
                <div class="panel-card shadow-sm">
                    <div class="panel-card-header">
                        <h5>Recent Emergency Requests</h5>
                        <button class="btn btn-outline-primary btn-sm py-1 px-3" onclick="pollData()">
                            <i class="fa-solid fa-rotate me-1"></i> Refresh
                        </button>
                    </div>
                    <div class="panel-card-body table-responsive">
                        <table class="table admin-table mb-0">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Date/Time</th>
                                    <th>Status</th>
                                    <th>Gesture Used</th>
                                    <th>Location</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="emergencies-table-body">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Loading live alerts...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Registered Users list (col-lg-4) -->
            <div class="col-lg-4">
                <div class="panel-card shadow-sm">
                    <div class="panel-card-header bg-white">
                        <h5>Registered Users</h5>
                        <span class="badge bg-secondary rounded-pill" id="registered-users-badge"><?php echo count($registered_users); ?></span>
                    </div>
                    <div class="panel-card-body p-3" style="max-height: 520px; overflow-y: auto;">
                        <?php if (empty($registered_users)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fa-solid fa-user-slash fs-2 mb-2 d-block opacity-50"></i>
                                No registered users found.
                            </div>
                        <?php else: ?>
                            <?php foreach ($registered_users as $user): 
                                $gesture = $user['selected_gesture'] ?? 'None Configured';
                                $emoji = $gesture_emojis[$gesture] ?? '❓';
                            ?>
                                <div class="user-list-card border">
                                    <div>
                                        <h6><?php echo h($user['name']); ?></h6>
                                        <p><i class="fa-solid fa-envelope me-1"></i> <?php echo h($user['email']); ?></p>
                                        <p><i class="fa-solid fa-phone me-1"></i> <?php echo h($user['phone']); ?></p>
                                    </div>
                                    <div class="text-end" style="min-width: 90px;">
                                        <span class="badge bg-light text-dark border p-2 text-wrap" style="font-size: 10px; font-weight: 500;">
                                            SOS: <?php echo $emoji . ' ' . h($gesture); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals removed in favor of native dialogs to guarantee blocking UI wait -->

    <!-- Footer -->
    <div class="footer-text" style="color: rgba(0,0,0,0.5); padding-top: 20px;">
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AJAX Polling Script -->
    <script>
        let isPollingPaused = false;

        const gestureEmojis = {
            'One Finger': '☝️',
            'Two Fingers': '✌️',
            'Thumbs Up': '👍',
            'Fist': '✊'
        };

        function pollData() {
            if (isPollingPaused) return;
            fetch('admin_api.php')
                .then(res => res.json())
                .then(data => {
                    if (isPollingPaused) return; // Discard response if polling was paused during the request
                    if (data.success) {
                        // Update Stat cards
                        document.getElementById('stat-total-users').textContent = data.stats.total_users;
                        document.getElementById('stat-total-alerts').textContent = data.stats.total_alerts;
                        
                        const activeCount = data.stats.active_emergencies;
                        document.getElementById('stat-active-emergencies').textContent = activeCount;
                        
                        // Pulse the active emergency card if there is any active alarm!
                        const activeCard = document.getElementById('active-emergencies-card');
                        if (activeCount > 0) {
                            activeCard.style.animation = 'pulse-alert 1.5s infinite';
                        } else {
                            activeCard.style.animation = 'none';
                        }

                        // Update table
                        const tbody = document.getElementById('emergencies-table-body');
                        if (data.logs.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-clock-rotate-left fs-3 mb-2 d-block opacity-40"></i>
                                        No emergency alerts logged in database.
                                    </td>
                                </tr>
                            `;
                            return;
                        }

                        let html = '';
                        data.logs.forEach(log => {
                            const isEmergency = log.status === 'Emergency Active';
                            const badgeClass = isEmergency ? 'status-badge-active' : 'status-badge-cancelled';
                            const badgeLabel = isEmergency ? 'Active' : 'Cancelled';
                            const emoji = gestureEmojis[log.gesture_used] || '✌️';
                            
                            // Row styling for active emergencies
                            const rowBg = isEmergency ? 'style="background-color: rgba(220, 38, 38, 0.03); font-weight: 500;"' : '';
                            
                            // Action button
                            const actionBtn = isEmergency ? 
                                `<button type="button" class="btn btn-outline-danger btn-sm py-1 px-3" onclick="resolveEmergency(event, ${log.id})">Resolve</button>` :
                                `<span class="text-muted small"><i class="fa-solid fa-check text-success me-1"></i> Closed</span>`;

                            html += `
                                <tr ${rowBg}>
                                    <td class="fw-bold">${escapeHtml(log.user_name)}</td>
                                    <td>
                                        <div class="small">${log.date}</div>
                                        <div class="text-muted small">${log.time}</div>
                                    </td>
                                    <td>
                                        <span class="${badgeClass}">${badgeLabel}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border p-2">
                                            ${emoji} ${escapeHtml(log.gesture_used)}
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fa-solid fa-location-dot text-danger me-1 small"></i>
                                        <span class="text-muted">${escapeHtml(log.location)}</span>
                                    </td>
                                    <td>${actionBtn}</td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    } else {
                        console.error("API error:", data.message);
                        if (data.message === 'Unauthorized') {
                            window.location.href = 'admin_login.php';
                        }
                    }
                })
                .catch(err => {
                    console.error("AJAX Polling failed: ", err);
                });
        }

        function resolveEmergency(event, logId) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // 1. Pause background polling to prevent DOM updates during interaction
            isPollingPaused = true;
            
            // 2. Ask user via native confirm (blocking dialog, guarantees to hold and wait for click)
            const confirmed = confirm("Are you sure you want to resolve and close this emergency alert?");
            
            if (!confirmed) {
                // If user clicks Cancel, resume background polling and exit
                isPollingPaused = false;
                return;
            }
            
            // 3. Send resolve request to API
            const formData = new FormData();
            formData.append('action', 'resolve');
            formData.append('log_id', logId);

            fetch('admin_api.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 4. Show native alert on success (blocking dialog, guarantees to hold until OK clicked)
                    alert("Emergency alert successfully resolved and closed.");
                } else {
                    alert("Error: " + data.message);
                }
                // 5. Resume background polling and refresh table
                isPollingPaused = false;
                pollData();
            })
            .catch(err => {
                console.error("Resolve error:", err);
                alert("Network error: Could not resolve emergency.");
                isPollingPaused = false;
                pollData();
            });
        }

        // Helper function to escape HTML string
        function escapeHtml(string) {
            return String(string).replace(/&/g, '&amp;')
                                 .replace(/</g, '&lt;')
                                 .replace(/>/g, '&gt;')
                                 .replace(/"/g, '&quot;')
                                 .replace(/'/g, '&#039;');
        }

        // Poll immediately on load, then every 5 seconds
        document.addEventListener('DOMContentLoaded', () => {
            pollData();
            setInterval(pollData, 5000);
        });
    </script>
</body>
</html>
