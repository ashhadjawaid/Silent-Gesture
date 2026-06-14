<?php
/**
 * Admin API Endpoint
 * Provides JSON data for Admin Dashboard live polling and action controls
 */
require_once 'config.php';

header('Content-Type: application/json');

// Check authorization
if (!is_admin_authenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Handle Admin Actions (e.g. resolving/cancelling alert)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'resolve') {
        $log_id = intval($_POST['log_id'] ?? 0);
        if ($log_id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE emergency_logs SET status = 'Cancelled' WHERE id = ?");
                $stmt->execute([$log_id]);
                echo json_encode(['success' => true, 'message' => 'Emergency status updated to Cancelled.']);
                exit;
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid log ID.']);
            exit;
        }
    }
}

// Default GET: Fetch dashboard statistics and recent logs
try {
    // 1. Total users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $total_users = $stmt->fetch()['count'];
    
    // 2. Total alerts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM emergency_logs");
    $total_alerts = $stmt->fetch()['count'];
    
    // 3. Active emergencies
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM emergency_logs WHERE status = 'Emergency Active'");
    $active_emergencies = $stmt->fetch()['count'];
    
    // 4. Recent logs
    $stmt = $pdo->query("SELECT el.*, u.name as user_name FROM emergency_logs el JOIN users u ON el.user_id = u.id ORDER BY el.id DESC LIMIT 15");
    $logs = $stmt->fetchAll();
    
    // Format times and dates for JSON return
    $formatted_logs = [];
    foreach ($logs as $log) {
        $formatted_logs[] = [
            'id' => $log['id'],
            'user_name' => $log['user_name'],
            'date' => date('Y-m-d', strtotime($log['date'])),
            'time' => date('h:i A', strtotime($log['time'])),
            'status' => $log['status'],
            'gesture_used' => $log['gesture_used'],
            'location' => $log['location'] ?? 'Available'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => $total_users,
            'total_alerts' => $total_alerts,
            'active_emergencies' => $active_emergencies
        ],
        'logs' => $formatted_logs
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database query error: ' . $e->getMessage()]);
}
?>
