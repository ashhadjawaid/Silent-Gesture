<?php
/**
 * Admin Logout Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Unset admin session variables
if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['admin_id']);
}
if (isset($_SESSION['admin_email'])) {
    unset($_SESSION['admin_email']);
}

// Redirect to admin login page
header("Location: admin_login.php");
exit;
?>
