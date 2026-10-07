<?php
// Read Registrations[cite: 4]
$regFile = __DIR__ . '/data/registrations.json';
$registrations = [];
if (file_exists($regFile)) {
    $registrations = json_decode(file_get_contents($regFile), true) ?: [];
}

// Read Contacts[cite: 4]
$contactFile = __DIR__ . '/data/contacts.json';
$contacts = [];
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
    </style>
</head>
<body>
    <div class="layout">
        <!-- GLOBAL SIDEBAR -->
        <aside id="sidebar">
            <div class="logo"><h2>STUDENTHUB</h2><p>Admin Portal</p></div>
            <nav>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 10px 0 5px 15px; font-weight: bold; letter-spacing: 1px;">MAIN MENU</p>
                <a href="home.html">Home</a>
                <a href="dashboard.html">Dashboard</a>
                
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
                <a href="login.html">Login</a>
                <a href="register.php">Register</a>
                <a href="admin.php" class="active">Admin Panel</a>
            </nav>
            <div class="user-profile">
                <p>Om Pithadiya</p><span>System Administrator</span>
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
            <p class="subtitle">Live data view of JSON form submissions.</p>

            <!-- Registrations Display[cite: 4] -->
            <div class="card">
                <h3 style="margin-bottom: 20px;">New Student Registrations (JSON)</h3>
                <?php if (empty($registrations)): ?>
                    <div class="empty-state">No registrations found in data/registrations.json.</div>
                <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Course</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($registrations) as $reg): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reg['registration_date'] ?? ''); ?></td>
                                    <td><strong><?php echo htmlspecialchars($reg['name'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($reg['email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($reg['mobile'] ?? ''); ?></td>
                                    <td><span class="tag academic"><?php echo htmlspecialchars($reg['course'] ?? ''); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Support Requests Display[cite: 4] -->
            <div class="card">
                <h3 style="margin-bottom: 20px;">Support Requests (JSON)</h3>
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
                            <?php foreach (array_reverse($contacts) as $contact): ?>
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