<?php
session_start();
require_once '../config/database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($username) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE (email = ? OR full_name = ?) AND role = 'admin'");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];

                // Log audit trail for login
                $userId = $user['id'];
                $action = "ADMIN_LOGIN";
                $details = "Administrator logged into the panel.";
                $ip = $_SERVER['REMOTE_ADDR'];

                $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
                $logStmt->bind_param("isss", $userId, $action, $details, $ip);
                $logStmt->execute();
                $logStmt->close();

                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Invalid password.";
            }
        } else {
            $message = "Invalid username or account lacks administrator privileges.";
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
    <title>StudentHub Admin Login</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { background-color: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 420px; border: 1px solid #e2e8f0; }
        .login-card h2 { margin-bottom: 25px; color: #1e293b; font-size: 1.5rem; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>StudentHub Admin Login</h2>
        <?php if (!empty($message)): ?>
            <div class="error-msg"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Username / Email</label>
                <input type="text" name="username" required placeholder="Enter admin email or username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter password">
            </div>
            <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Log In</button>
        </form>
    </div>
</body>
</html>