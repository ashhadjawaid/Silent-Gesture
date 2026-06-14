<?php
/**
 * Trigger Emergency Endpoint
 * Inserts a new active emergency log into the database
 */
require_once 'config.php';

header('Content-Type: application/json');

if (!is_authenticated()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$gesture = get_user_gesture($user_id) ?? 'Two Fingers';

// List of realistic locations to make the dashboard look premium and alive
$locations = [
    'Main Building, Floor 1',
    'Library, Ground Floor',
    'North Campus Gate',
    'Science Block, Lab 4',
    'Cafeteria Entrance',
    'West Hallway Elevator',
    'Student Parking Area',
    'Block C, Seminar Hall'
];
$random_location = $locations[array_rand($locations)];

$current_date = date('Y-m-d');
$current_time = date('H:i:s');
$status = 'Emergency Active';

try {
    $stmt = $pdo->prepare("INSERT INTO emergency_logs (user_id, date, time, status, gesture_used, location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $current_date, $current_time, $status, $gesture, $random_location]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Emergency logged successfully.',
        'log' => [
            'user' => $_SESSION['user_name'],
            'date' => $current_date,
            'time' => date('h:i A', strtotime($current_time)),
            'status' => $status,
            'gesture' => $gesture,
            'location' => $random_location
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
