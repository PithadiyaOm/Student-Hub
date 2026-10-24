<?php
require_once 'auth.php';

// 1. Restrict access: Only logged-in users with 'admin' role can view this page
requireLogin('admin');

// 2. Fetch all registered users from MySQL database
$dbUsers = [];
$userQuery =$conn->query("SELECT id, full_name, email, mobile, course, year_of_study, gender, role, created_at FROM users ORDER BY id DESC");
if ($userQuery) {
    while ($row =$userQuery->fetch_assoc()) {
        $dbUsers[] =$row;
    }
}

// 3. Read Contact Support Requests from JSON (Practical 7)
$contactFile = __DIR__ . '/data/contacts.json';$contacts = [];
if (file_exists($contactFile)) {
    $contacts = json_decode(file_get_contents($contactFile), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Admin Panel</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        .admin-table th, .admin-table td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); text-align: left; }
        .admin-table th { color: var(--text-muted); font-size: 0.85rem; font-weight: bold; background-color: var(--bg-body); text-transform: uppercase; }
        .empty-state { text-align: center; color: var(--text-muted); padding: 20px; font-style: italic; }
        .role-badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .role-admin { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }
        .role-student { background-color: rgba(40, 167, 69, 0.15); color: #28a745; }
    </style>
</head>
<body>
    <div id="notification-banner">
        <span>🔔 Admin Mode Active: Logged in as <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <button id="close-banner">&times;</button>
    </div>

    <div class="layout">
        <!-- GLOBAL SIDEBAR -->
        <aside id="sidebar">
            <div class="logo"><h2>STUDENTHUB</h2><p>Admin Portal</p></div>
            <nav>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 10px 0 5px 15px; font-weight: bold; letter-spacing: 1px;">MAIN MENU</p>
                <a href="home.html">Home</a>
                <a href="dashboard.php">Student Dashboard</a>
                
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
                <a href="admin.php" class="active">Admin Panel</a>
                <a href="logout.php">Logout</a>
            </nav>
            <div class="user-profile">
                <p><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <span><?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                <a href="logout.php" style="color: #d93025; font-weight: bold;">Sign out</a>
            </div>
        </aside>

        <main>
            <div class="top-header">
                <div class="controls">
                    <button id="hamburger-btn">☰ Menu</button>
                    <button id="theme-toggle">🌙 Dark</button>
                </div>
                <div class="breadcrumbs">Admin / Dashboard</div>
            </div>

            <h1>Admin Dashboard</h1>
            <p class="subtitle">
                Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> · 
                Role-based administrative view for managing StudentHub users and support tickets.
            </p>

            <!-- Summary Cards -->
            <div class="grid-3">
                <div class="card">
                    <p class="breadcrumbs">REGISTERED USERS (MYSQL)</p>
                    <h2 style="font-size: 2.5rem; margin: 10px 0;"><?php echo count($dbUsers); ?></h2>
                    <p class="subtitle" style="margin:0;">Active accounts in database</p>
                </div>
                <div class="card">
                    <p class="breadcrumbs">SUPPORT TICKETS (JSON)</p>
                    <h2 style="font-size: 2.5rem; margin: 10px 0;"><?php echo count($contacts); ?></h2>
                    <p class="subtitle" style="margin:0;">Submitted via contact.php</p>
                </div>
                <div class="card">
                    <p class="breadcrumbs">SESSION STATUS</p>
                    <h2 style="font-size: 2.5rem; margin: 10px 0; color: #28a745;">Active</h2>
                    <p class="subtitle" style="margin:0;">15-min inactivity timeout</p>
                </div>
            </div>

            <!-- MySQL Registered Users Table -->
            <div class="card" style="overflow-x: auto;">
                <h3 style="margin-bottom: 10px;">Registered Users (MySQL `users` Table)</h3>
                <?php if (empty($dbUsers)): ?>
                    <div class="empty-state">No users found in the MySQL database.</div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Role</th>
                                <th>Registered On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dbUsers as$u): ?>
                                <tr>
                                    <td>#<?php echo (int)$u['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($u['full_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><?php echo htmlspecialchars($u['mobile']); ?></td>
                                    <td><span class="tag academic"><?php echo htmlspecialchars($u['course']); ?></span></td>
                                    <td><?php echo htmlspecialchars($u['year_of_study']); ?></td>
                                    <td>
                                        <span class="role-badge <?php echo $u['role'] === 'admin' ? 'role-admin' : 'role-student'; ?>">
                                            <?php echo htmlspecialchars($u['role']); ?>
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;"><?php echo htmlspecialchars($u['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- JSON Support Requests Table -->
            <div class="card" style="overflow-x: auto;">
                <h3 style="margin-bottom: 10px;">Support Requests (`data/contacts.json`)</h3>
                <?php if (empty($contacts)): ?>
                    <div class="empty-state">No support requests found in data/contacts.json.</div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th>Subject</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($contacts) as$contact): ?>
                                <tr>
                                    <td style="white-space: nowrap;"><?php echo htmlspecialchars($contact['date'] ?? ''); ?></td>
                                    <td><span class="tag event"><?php echo htmlspecialchars($contact['category'] ?? ''); ?></span></td>
                                    <td><strong><?php echo htmlspecialchars($contact['subject'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($contact['details'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="JS/script.js"></script>
</body>
</html>