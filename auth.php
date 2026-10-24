<?php
// Start session securely with strict cookie parameters
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true, // Prevents JavaScript access to session cookie
        'samesite' => 'Lax'
    ]);
    session_start();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "studenthub";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// 1. Check Session Timeout (15 minutes = 900 seconds)
$timeout_duration = 900; 
if (isset($_SESSION['last_activity'])) {
    if ((time() - $_SESSION['last_activity']) > $timeout_duration) {
        session_unset();
        session_destroy();
        header("Location: login.php?error=timeout");
        exit();
    }
}
$_SESSION['last_activity'] = time(); // Update last activity timestamp

// 2. Check "Remember Me" Cookie if not logged in via session
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("SELECT id, full_name, email, role FROM users WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['last_activity'] = time();
    }
    $stmt->close();
}

// Function to restrict access and check roles
function requireLogin($requiredRole = null) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=unauthorized");
        exit();
    }
    if ($requiredRole !== null && $_SESSION['user_role'] !== $requiredRole) {
        // Redirect to appropriate dashboard if role doesn't match
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    }
}
?>