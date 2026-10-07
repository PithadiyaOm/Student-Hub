<?php
$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Extract variables
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");

    // 1. Server-side Validation[cite: 4]
    if ($name === "" || $email === "" || $mobile === "" || $course === "") {
        $message = "Please fill all required fields.";
        $messageType = "error-alert";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error-alert";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $message = "Mobile number must contain exactly 10 digits.";
        $messageType = "error-alert";
    } else {
        // 2. Sanitization[cite: 4]
        $name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
        $email = htmlspecialchars($email, ENT_QUOTES, "UTF-8");
        $mobile = htmlspecialchars($mobile, ENT_QUOTES, "UTF-8");
        $course = htmlspecialchars($course, ENT_QUOTES, "UTF-8");

        // 3. File Writing (JSON Format)[cite: 4]
        $file = __DIR__ . "/data/registrations.json";
        $registrations = [];

        // Read existing JSON data if available
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $registrations = $decoded;
            }
        }

        // Append new record
        $registrations[] = [
            "name" => $name,
            "email" => $email,
            "mobile" => $mobile,
            "course" => $course,
            "registration_date" => date("Y-m-d H:i:s")
        ];

        // Save safely back to JSON using LOCK_EX[cite: 4]
        if (file_put_contents($file, json_encode($registrations, JSON_PRETTY_PRINT), LOCK_EX) !== false) {
            $message = "Registration successful!";
            $messageType = "success-alert";
        } else {
            $message = "Unable to save registration.";
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
    <title>StudentHub - Register</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* PHP Alert Message Styles */
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
                <a href="contact.php">Contact</a>
                <a href="feedback.html">Feedback</a>
                
                <p style="font-size: 0.75rem; color: var(--text-muted); margin: 20px 0 5px 15px; font-weight: bold; letter-spacing: 1px;">ACCOUNT</p>
                <a href="login.html">Login</a>
                <a href="register.php" class="active">Register</a>
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
                <div class="breadcrumbs">Home / Register</div>
            </div>

            <p class="breadcrumbs">ACCOUNT</p>
            <h1>Student Registration</h1>
            <p class="subtitle">Create your StudentHub student account.</p>

            <div class="form-box">
                <!-- PHP Message Output[cite: 4] -->
                <?php if ($message !== ""): ?>
                    <div class="<?php echo $messageType; ?>"><?php echo $message; ?></div>
                <?php endif; ?>

                <!-- Form submits to itself using POST[cite: 4] -->
                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" maxlength="10" required>
                    </div>
                    <div class="form-group">
                        <label for="course">Course</label>
                        <select id="course" name="course" required>
                            <option value="">Select Course</option>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Computer Science">Computer Science</option>
                        </select>
                    </div>
                    <button type="submit" class="btn" style="width: 100%;">Register</button>
                </form>
            </div>
        </main>
    </div>
    <script src="JS/script.js"></script>
</body>
</html>