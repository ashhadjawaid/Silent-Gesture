<?php
/**
 * Login Page
 * Silent Gesture Recognition Emergency Safety Web Application
 */
require_once 'config.php';

// If already logged in, redirect based on gesture setup status
if (is_authenticated()) {
    $gesture = get_user_gesture($_SESSION['user_id']);
    if (empty($gesture)) {
        header("Location: gesture_setup.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$errors = [];
$success_msg = '';

if (isset($_GET['registered'])) {
    $success_msg = "Registration successful! Please login below.";
}

$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Remember Me Cookie Logic
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    // Update database with token
                    $update_stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $update_stmt->execute([$token, $user['id']]);

                    // Set cookie valid for 30 days
                    setcookie('remember_me', $token, time() + (86400 * 30), "/", "", false, true);
                }

                // Redirect based on gesture configuration
                $gesture = get_user_gesture($user['id']);
                if (empty($gesture)) {
                    header("Location: gesture_setup.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $errors[] = "Invalid email or password.";
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
    <title>Login - Silent Gesture Emergency System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <span class="fs-4 fw-bold text-uppercase">🛡️ Silent Gesture System</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link active" href="login.php">Login</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-outline-custom py-1 px-3" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Login Section -->
    <div class="auth-container">
        <div class="security-card">
            <div class="security-card-header">
                <h2>Welcome Back</h2>
                <p>Login to activate silently the emergency safety monitor</p>
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

                <?php if ($success_msg): ?>
                    <div class="alert alert-success">
                        <?php echo h($success_msg); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="john@example.com" value="<?php echo h($email); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label text-muted" for="remember">Remember Me</label>
                        </div>
                        <a href="#" class="text-decoration-none text-primary fw-semibold" onclick="alert('Please contact your administrator to reset your password.')">Forgot Password?</a>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>

                    <div class="text-center mt-3">
                        <span class="text-muted">Don't have an account? </span>
                        <a href="register.php" class="text-decoration-none fw-semibold">Register</a>
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
