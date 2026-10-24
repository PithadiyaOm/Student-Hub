<?php
session_start();
require_once '../config/database.php';

// 1. Role-Based Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Log dashboard view in Audit Log
$userId = $_SESSION['user_id'];
$action = "VIEW_DASHBOARD";
$details = "Admin opened the dashboard";
$ip = $_SERVER['REMOTE_ADDR'];

$logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
$logStmt->bind_param("isss", $userId, $action, $details, $ip);
$logStmt->execute();
$logStmt->close();

// 2. Fetch Dynamic Database Statistics
// Count Admin Accounts
$adminCountRes = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
$adminCount = $adminCountRes ? $adminCountRes->fetch_assoc()['total'] : 1;

// Count Events
$eventCount = "N/A";
$eventQuery = $conn->query("SELECT COUNT(*) as total FROM events_management");
if ($eventQuery) {
    $eventCount = $eventQuery->fetch_assoc()['total'];
}

// Count Students
$studentCount = "N/A";
$studentQuery = $conn->query("SELECT COUNT(*) as total FROM practical_students WHERE status='active'");
if ($studentQuery) {
    $studentCount = $studentQuery->fetch_assoc()['total'];
}

// Count Registrations / Records
$regCount = "N/A";
$regQuery = $conn->query("SELECT COUNT(*) as total FROM practical_students");
if ($regQuery) {
    $regCount = $regQuery->fetch_assoc()['total'];
}

// Determine role for dynamic menu
$currentRole = $_SESSION['user_role'] ?? 'guest';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background-color: #f8fafc; margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background-color: #0f172a; color: #fff; display: flex; flex-direction: column; }
        .admin-sidebar .logo-area { padding: 25px 20px; font-size: 1.25rem; font-weight: bold; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid #1e293b; }
        .admin-sidebar .menu-label { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; padding: 20px 20px 10px; font-weight: bold; }
        .admin-sidebar nav a { display: block; padding: 12px 20px; color: #cbd5e1; text-decoration: none; font-weight: 500; transition: background 0.2s; }
        .admin-sidebar nav a:hover, .admin-sidebar nav a.active { background-color: #2563eb; color: #fff; border-radius: 6px; margin: 0 10px; }
        .admin-main { flex-grow: 1; padding: 30px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .stat-card .badge-type { position: absolute; top: 20px; right: 20px; font-size: 0.75rem; color: #64748b; font-weight: bold; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; }
        .stat-card h2 { font-size: 2.2rem; margin: 10px 0; color: #0f172a; }
        .stat-card p { color: #64748b; margin: 0; font-size: 0.9rem; font-weight: 500; }
        .info-card { background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Dynamic Navigation Menu -->
        <aside class="admin-sidebar">
            <div class="logo-area">🎓 StudentHub</div>
            <div class="menu-label">Main Menu</div>
            <nav style="padding: 0 10px;">
                <a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">🏠 Dashboard</a>
                
                <?php if ($currentRole === 'admin'): ?>
                    <a href="admin-events.php" class="<?php echo ($currentPage == 'admin-events.php') ? 'active' : ''; ?>">📅 Manage Events</a>
                    <a href="student-management.php" class="<?php echo ($currentPage == 'student-management.php') ? 'active' : ''; ?>">👥 Manage Students</a>
                    <a href="audit-logs.php" class="<?php echo ($currentPage == 'audit-logs.php') ? 'active' : ''; ?>">📋 Audit Logs</a>
                <?php endif; ?>

                <div style="margin-top: 40px; border-top: 1px solid #1e293b; padding-top: 20px;">
                    <a href="logout.php" style="color: #f87171;">🚪 Logout</a>
                </div>
            </nav>
            <div style="padding: 20px; font-size: 0.8rem; color: #64748b; margin-top: auto;">
                Role-Based Access Control enabled
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            <div class="top-bar">
                <div>
                    <h1 style="margin: 0; font-size: 1.5rem; color: #0f172a;">Admin Dashboard</h1>
                    <p style="margin: 5px 0 0; color: #64748b; font-size: 0.9rem;">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                </div>
                <div>
                    <span class="status-badge badge-blue">Administrator</span>
                </div>
            </div>

            <!-- Dashboard Summary Cards -->
            <div class="stat-grid">
                <div class="stat-card">
                    <span class="badge-type">Accounts</span>
                    <h2><?php echo $adminCount; ?></h2>
                    <p>Admin Accounts</p>
                </div>
                <div class="stat-card">
                    <span class="badge-type">Events</span>
                    <h2><?php echo $eventCount; ?></h2>
                    <p>StudentHub Events</p>
                </div>
                <div class="stat-card">
                    <span class="badge-type">Students</span>
                    <h2><?php echo $studentCount; ?></h2>
                    <p>Registered Students</p>
                </div>
                <div class="stat-card">
                    <span class="badge-type">Records</span>
                    <h2><?php echo $regCount; ?></h2>
                    <p>Registrations</p>
                </div>
            </div>

            <!-- Role-Based Access Control Info Card -->
            <div class="info-card">
                <h3 style="margin-top: 0; color: #0f172a;">Role-Based Access Control</h3>
                <p style="color: #475569; font-size: 0.95rem;">Your current role is <strong>Administrator</strong>. Protected dashboard access requires a valid admin session[cite: 19].</p>
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <span class="status-badge badge-green">Authenticated</span>
                    <span class="status-badge badge-blue">Admin Role</span>
                    <span class="status-badge badge-green">Session Active</span>
                </div>
            </div>

            <!-- Audit Log Quick Card -->
            <div class="info-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin-top: 0; color: #0f172a; margin-bottom: 5px;">Audit Log</h3>
                        <p style="color: #64748b; margin: 0; font-size: 0.9rem;">Review recorded administrator activities[cite: 19].</p>
                    </div>
                    <a href="audit-logs.php" class="btn" style="text-decoration: none; padding: 8px 16px; font-size: 0.9rem;">View Audit Logs</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>