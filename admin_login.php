<?php
/**
 * Admin Login Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// Disable browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// If already logged in, redirect to admin dashboard
if (is_admin_authenticated()) {
    header("Location: admin_dashboard.php");
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Password is correct, start admin session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];

                header("Location: admin_dashboard.php");
                exit;
            } else {
                $errors[] = "Invalid admin credentials.";
            }
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
    <title>Admin Login - Silent Gesture Emergency System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .security-card-header {
            /* Distinct purple-burgundy/navy gradient for admin access */
            background: linear-gradient(135deg, #1e1b4b, #0f172a) !important;
            border-bottom: 4px solid var(--primary-blue) !important;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <span class="fs-4 fw-bold text-uppercase">🛡️ Silent Gesture Admin Portal</span>
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">User Portal</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Admin Login Section -->
    <div class="auth-container">
        <div class="security-card">
            <div class="security-card-header">
                <h2><i class="fa-solid fa-user-shield me-2"></i>Admin Login</h2>
                <p>Enter administrator credentials to access dashboard panel</p>
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

                <form action="admin_login.php" method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="email" class="form-label">Admin Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your admin email" value="<?php echo h($email); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary" style="background-color: #3b82f6; border-color: #3b82f6;">Login</button>
                    </div>

                    <div class="text-center mt-3">
                        <span class="text-muted">Return to </span>
                        <a href="login.php" class="text-decoration-none fw-semibold">User Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-text">
        <p>&copy; 2026 Silent Gesture Emergency Safety Web Application. All rights reserved.</p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
