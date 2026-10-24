<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$logs = [];
$result = $conn->query("SELECT * FROM audit_logs ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Admin - Audit Logs</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; }
        .top-nav { background: #0f172a; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .top-nav a { color: #cbd5e1; text-decoration: none; margin-left: 20px; font-weight: 500; font-size: 0.9rem; }
        .top-nav a:hover { color: #fff; }
        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .table-card { background: #fff; border-radius: 8px; border: 1px solid #e2e8f0; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .log-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        .log-table th, .log-table td { padding: 12px 15px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .log-table th { background-color: #f1f5f9; color: #475569; font-size: 0.85rem; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="top-nav">
        <div><strong>StudentHub Admin</strong></div>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="audit-logs.php" style="color: #fff; text-decoration: underline;">Audit Logs</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="table-card">
            <h2>Administrator Audit Logs</h2>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 20px;">Recent activities recorded by the system[cite: 19].</p>

            <table class="log-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" style="text-align: center; color: #64748b; padding: 20px;">No audit logs recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td>#<?php echo $log['log_id']; ?></td>
                                <td><?php echo $log['user_id']; ?></td>
                                <td><span class="tag academic" style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:4px; font-weight:bold;"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                <td><?php echo htmlspecialchars($log['details']); ?></td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td style="white-space: nowrap;"><?php echo $log['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top: 25px;">
                <a href="dashboard.php" class="btn" style="text-decoration: none; display: inline-block;">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>