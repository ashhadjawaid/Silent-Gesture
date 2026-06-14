<?php
/**
 * Database Configuration and Session Setup
 * Silent Gesture Recognition Emergency Safety Web Application
 */

// Enable session security settings before starting session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

if (session_status() == PHP_SESSION_NONE) {
    // Differentiate session name for admin and user portals to prevent cookie collisions on localhost
    $current_script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    if (strpos($current_script, 'admin_') === 0) {
        session_name('SILENT_GESTURE_ADMIN_SESS');
    } else {
        session_name('SILENT_GESTURE_USER_SESS');
    }
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'silent_emergency');

$pdo = null;
$db_type = 'mysql';

try {
    // Attempt MySQL connection
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // FALLBACK: SQLite database for offline/portable execution
    $db_type = 'sqlite';
    $sqlite_file = __DIR__ . '/database.sqlite';
    
    try {
        $pdo = new PDO("sqlite:" . $sqlite_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Auto-create tables in SQLite
        create_sqlite_tables($pdo);
    } catch (PDOException $sqle) {
        die("Database Connection Error: " . $sqle->getMessage());
    }
}

/**
 * Creates database tables for SQLite fallback
 */
function create_sqlite_tables($pdo) {
    // 1. Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        phone TEXT NOT NULL,
        password TEXT NOT NULL,
        remember_token TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Gesture settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS gesture_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        selected_gesture TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 3. Emergency contacts table
    $pdo->exec("CREATE TABLE IF NOT EXISTS emergency_contacts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        relation TEXT NOT NULL,
        phone TEXT NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 4. Emergency logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS emergency_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        date TEXT NOT NULL,
        time TEXT NOT NULL,
        status TEXT NOT NULL,
        gesture_used TEXT NOT NULL,
        location TEXT DEFAULT 'Available',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Ensure 'location' column exists (migration helper for existing databases)
    try {
        $pdo->exec("SELECT location FROM emergency_logs LIMIT 1");
    } catch (PDOException $e) {
        try {
            $pdo->exec("ALTER TABLE emergency_logs ADD COLUMN location TEXT DEFAULT 'Available'");
        } catch (PDOException $e2) {
            // Ignore error if it somehow failed
        }
    }

    // 5. Admin table
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Insert default admin if not exists (password: admin123)
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM admin WHERE email = 'admin@emergency.com'");
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row['count'] == 0) {
        $admin_pass = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt_insert = $pdo->prepare("INSERT INTO admin (email, password) VALUES ('admin@emergency.com', ?)");
        $stmt_insert->execute([$admin_pass]);
    }
}

/**
 * Handle "Remember Me" session restoration
 */
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        
        // Refresh cookie lifetime (30 days)
        setcookie('remember_me', $token, time() + (86400 * 30), "/", "", false, true);
    }
}

/**
 * Check if the user is authenticated
 */
function is_authenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Require authentication or redirect to login
 */
function require_auth() {
    if (!is_authenticated()) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Get user's selected gesture if exists
 */
function get_user_gesture($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT selected_gesture FROM gesture_settings WHERE user_id = ? LIMIT 1");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    return $result ? $result['selected_gesture'] : null;
}

/**
 * Sanitize output helper
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Check if the admin is authenticated
 */
function is_admin_authenticated() {
    return isset($_SESSION['admin_id']);
}

/**
 * Require admin authentication or redirect to admin login
 */
function require_admin_auth() {
    if (!is_admin_authenticated()) {
        header("Location: admin_login.php");
        exit;
    }
}
?>
