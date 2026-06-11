<?php
/**
 * Logout Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Revoke DB token if user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        // Fail silently and proceed with local logout
    }
}

// Unset all session values
$_SESSION = [];

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Expire Remember Me cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, "/");
}

// Destroy session on server
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
