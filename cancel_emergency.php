<?php
/**
 * Cancel Emergency Endpoint
 * Updates active emergency logs to Cancelled status
 */
require_once 'config.php';

header('Content-Type: application/json');

if (!is_authenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Update active emergency logs to 'Cancelled'
    $stmt = $pdo->prepare("UPDATE emergency_logs SET status = 'Cancelled' WHERE user_id = ? AND status = 'Emergency Active'");
    $stmt->execute([$user_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Emergency state reset successfully.'
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
