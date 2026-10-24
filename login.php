<?php
require_once 'auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['user_role'] === 'admin' ? 'admin.php' : 'dashboard.php'));
    exit();
}

$message = "";
$messageType = "";

// Handle URL status messages
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthorized') {
        $message = "Access denied. Please log in to view the dashboard.";
        $messageType = "error-alert";
    } elseif ($_GET['error'] === 'timeout') {
        $message = "Your session has expired due to inactivity. Please log in again.";
        $messageType = "error-alert";
    }
} elseif (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $message = "You have been logged out securely.";
    $messageType = "success-alert";
}

// Process Login Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $pass = $_POST["password"] ?? "";
    $remember = isset($_POST["remember"]);

    if ($email === "" || $pass === "") {
        $message = "Please enter both email and password.";
        $messageType = "error-alert";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verify hashed password using password_verify()
            if (password_verify($pass, $user['password'])) {
                
                // Regenerate session ID to prevent session fixation attacks
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                // Optional Secure Remember-Me Token (Valid for 30 days)
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $upStmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $upStmt->bind_param("si", $token, $user['id']);
                    $upStmt->execute();
                    $upStmt->close();

                    setcookie('remember_token', $token, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

                // Role-Based Redirection
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();

            } else {
                $message = "Invalid email or password.";
                $messageType = "error-alert";
            }
        } else {
            $message = "No account found with that email address.";
            $messageType = "error-alert";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Secure Login (Practical 10)</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-alert { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; border: 1px solid #c3e6cb; }
        .error-alert { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div id="notification-banner">
        <span>🔔 StudentHub update: Mid-semester examinations begin September 8, 2026.</span>
        <button id="close-banner">&times;</button>
    </div>

    <div class="layout">
        <aside id="sidebar">
            <div class="logo"><h2>STUDENTHUB</h2><p>Student Portal</p></div>
            <nav>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 10px 0 5px 15px; font-weight: bold; letter-spacing: 1px;">MAIN MENU</p>
                <a href="home.html">Home</a>
                <a href="dashboard.php">Dashboard</a>
                
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 20px 0 5px 15px; font-weight: bold; letter-spacing: 1px;">ACADEMICS & CAMPUS</p>
                <a href="notices.html">Academic Notices</a>
                <a href="events.html">Campus Events</a>
                <a href="profile.html">Profile & Directory</a>
                <a href="faq.html">FAQ</a>
                
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 20px 0 5px 15px; font-weight: bold; letter-spacing: 1px;">INFO & SUPPORT</p>
                <a href="about.html">About</a>
                <a href="contact.php">Contact</a>
                <a href="feedback.html">Feedback</a>
                
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 20px 0 5px 15px; font-weight: bold; letter-spacing: 1px;">ACCOUNT</p>
                <a href="login.php" class="active">Login</a>
                <a href="register.php">Register</a>
                <a href="admin.php">Admin Panel</a>
            </nav>
        </aside>

        <main>
            <div class="top-header">
                <div class="controls">
                    <button id="hamburger-btn">☰ Menu</button>
                    <button id="theme-toggle">🌙 Dark</button>
                </div>
                <div class="breadcrumbs">Home / Login</div>
            </div>

            <p class="breadcrumbs">ACCOUNT</p>
            <h1>Student & Admin Login</h1>
            <p class="subtitle">Access your personalized StudentHub dashboard.</p>

            <div class="form-box">
                <?php if ($message !== ""): ?>
                    <div class="<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" placeholder="you@charusat.ac.in" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="form-group checkbox-group" style="margin-top: 15px; margin-bottom: 20px;">
                        <label>
                            <input type="checkbox" id="remember" name="remember">
                            Remember Me for 30 days
                        </label>
                    </div>

                    <button type="submit" class="btn">Sign In</button>
                </form>

                <p style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
                    Don't have an account? <a href="register.php" style="text-decoration: underline; font-weight: 600;">Create Account</a>
                </p>
            </div>
        </main>
    </div>
    <script src="JS/script.js"></script>
</body>
</html>