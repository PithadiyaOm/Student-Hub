<?php
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subject = trim($_POST["subject"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $details = trim($_POST["details"] ?? "");

    // 1. Validation
    if ($subject === "" || $category === "" || $details === "") {
        $message = "All fields are required.";
        $messageType = "error-alert";
    } else {
        // 2. Sanitization[cite: 4]
        $subject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8");
        $category = htmlspecialchars($category, ENT_QUOTES, "UTF-8");
        $details = htmlspecialchars($details, ENT_QUOTES, "UTF-8");

        // 3. File Writing (JSON)[cite: 4]
        $file = __DIR__ . "/data/contacts.json";
        $contacts = [];

        if (file_exists($file)) {
            $json = file_get_contents($file);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $contacts = $decoded;
            }
        }

        $contacts[] = [
            "subject" => $subject,
            "category" => $category,
            "details" => $details,
            "date" => date("Y-m-d H:i:s")
        ];

        if (file_put_contents($file, json_encode($contacts, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
            $message = "Support request submitted successfully.";
            $messageType = "success-alert";
        } else {
            $message = "Unable to save request.";
            $messageType = "error-alert";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Contact Support</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-alert { background-color: var(--success, #d4edda); color: var(--success-text, #155724); padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; border: 1px solid #c3e6cb; }
        .error-alert { background-color: var(--error, #f8d7da); color: var(--error-text, #721c24); padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="layout">
        <!-- GLOBAL SIDEBAR -->
        <aside id="sidebar">
            <div class="logo"><h2>STUDENTHUB</h2><p>Student Portal</p></div>
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
                <a href="contact.php" class="active">Contact</a>
                <a href="feedback.html">Feedback</a>
                
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 20px 0 5px 15px; font-weight: bold; letter-spacing: 1px;">ACCOUNT</p>
                <a href="login.html">Login</a>
                <a href="register.php">Register</a>
                <a href="admin.php">Admin Panel</a>
            </nav>
            <div class="user-profile">
                <p>Om Pithadiya</p><span>om@example.com</span><a href="#">Sign out</a>
            </div>
        </aside>

        <main>
            <div class="top-header">
                <div class="controls">
                    <button id="hamburger-btn">☰ Menu</button>
                    <button id="theme-toggle">🌙 Dark</button>
                </div>
                <div class="breadcrumbs">Home / Support</div>
            </div>

            <p class="breadcrumbs">SUPPORT</p>
            <h1>Contact StudentHub</h1>
            <p class="subtitle">Submit a support request to the administration team.</p>

            <div class="form-box">
                <!-- PHP Message Output[cite: 4] -->
                <?php if ($message !== ""): ?>
                    <div class="<?php echo $messageType; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Form submits to itself using POST[cite: 4] -->
                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Academic">Academic</option>
                            <option value="Attendance">Attendance</option>
                            <option value="Results">Results</option>
                            <option value="Portal Access">Portal Access</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea id="details" name="details" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">Submit Request</button>
                </form>
            </div>
        </main>
    </div>
    <script src="JS/script.js"></script>
</body>
</html>