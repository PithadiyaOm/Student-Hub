<?php
// 1. MySQLi Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "studenthub";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$messageType = "";

// 2. Backend Form Processing & Validation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $pass = $_POST["password"] ?? "";
    $confirm_pass = $_POST["confirm_password"] ?? "";
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year"] ?? "");
    $gender = trim($_POST["gender"] ?? "");
    $terms = isset($_POST["terms"]);
    $captcha_input = trim($_POST["captcha_input"] ?? "");
    $captcha_generated = trim($_POST["captcha_generated"] ?? "");

    // Consistent Backend Validations
    if ($name === "" || $email === "" || $mobile === "" || $pass === "" || $confirm_pass === "" || $course === "" || $year === "" || $gender === "") {
        $message = "Please fill in all required fields.";
        $messageType = "error-alert";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error-alert";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $message = "Mobile number must be a valid 10-digit number.";
        $messageType = "error-alert";
    } elseif (strlen($pass) < 8) {
        $message = "Password must be at least 8 characters long.";
        $messageType = "error-alert";
    } elseif ($pass !== $confirm_pass) {
        $message = "Passwords do not match.";
        $messageType = "error-alert";
    } elseif (!$terms) {
        $message = "You must agree to the Terms & Conditions and Privacy Policy.";
        $messageType = "error-alert";
    } elseif ($captcha_input === "" || $captcha_input !== $captcha_generated) {
        $message = "Security verification failed. Characters did not match.";
        $messageType = "error-alert";
    } else {
        // 3. Check for Duplicate Email in 'users' table
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "Registration rejected: Email address already exists!";
            $messageType = "error-alert";
        } else {
            // 4. Hash Password using password_hash()
            $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

            // 5. Insert User Details using MySQLi Prepared Statement
            $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, mobile, password, course, year_of_study, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssssss", $name, $email, $mobile, $hashedPassword, $course, $year, $gender);

            if ($insertStmt->execute()) {
                $message = "Account created successfully! Your password has been securely hashed and stored.";
                $messageType = "success-alert";
            } else {
                $message = "Database error: Unable to register user.";
                $messageType = "error-alert";
            }
            $insertStmt->close();
        }
        $checkStmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Secure Registration (Practical 9)</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-alert { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; border: 1px solid #c3e6cb; }
        .error-alert { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; border: 1px solid #f5c6cb; }
        
        /* CAPTCHA Box Styling */
        .captcha-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .captcha-box { 
            background-color: #eef2f7; 
            color: #1e293b; 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 1.4rem; 
            font-weight: 800; 
            letter-spacing: 6px; 
            padding: 10px 20px; 
            border-radius: 6px; 
            border: 1px dashed #94a3b8; 
            user-select: none;
            text-decoration: line-through;
        }
        .btn-refresh { 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            border: 1px solid var(--border-color); 
            padding: 10px 16px; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: 600; 
        }
        .btn-refresh:hover { background-color: var(--border-color); }
    </style>
</head>
<body>
    <div id="notification-banner">
        <span>🔔 StudentHub update: Mid-semester examinations begin September 8, 2026.</span>
        <button id="close-banner">&times;</button>
    </div>

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
                <p>Om Pithadiya</p><span>om@example.com</span><a href="login.html">Sign out</a>
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
            <h1>Registration Form</h1>
            <p class="subtitle">Create your StudentHub student account.</p>

            <div class="form-box">
                <?php if ($message !== ""): ?>
                    <div class="<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form id="prac9Form" action="register.php" method="POST" novalidate>
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        <span id="nameError" class="error-text"></span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" placeholder="you@charusat.ac.in" required>
                        <span id="emailError" class="error-text"></span>
                    </div>

                    <div class="form-group">
                        <label for="mobile">Mobile Number *</label>
                        <input type="tel" id="mobile" name="mobile" placeholder="10-digit mobile number" maxlength="10" required>
                        <span id="mobileError" class="error-text"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        <span id="passwordError" class="error-text"></span>
                        <div id="passwordStrength" class="password-strength"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                        <span id="confirmError" class="error-text"></span>
                    </div>

                    <div class="form-group">
                        <label for="course">Course *</label>
                        <select id="course" name="course" required>
                            <option value="">--Select your course--</option>
                            <option value="Computer Engineering">Computer Engineering</option>
                            <option value="Information Technology">Information Technology</option>
                            <option value="Computer Science">Computer Science</option>
                        </select>
                        <span id="courseError" class="error-text"></span>
                    </div>

                    <div class="form-group">
                        <label for="year">Year of Study *</label>
                        <select id="year" name="year" required>
                            <option value="">--Select your year--</option>
                            <option value="First Year">First Year</option>
                            <option value="Second Year">Second Year</option>
                            <option value="Third Year">Third Year</option>
                            <option value="Fourth Year">Fourth Year</option>
                        </select>
                        <span id="yearError" class="error-text"></span>
                    </div>

                    <div class="form-group radio-group-container">
                        <label>Gender *</label>
                        <div class="radio-group">
                            <label><input type="radio" name="gender" value="Male"> Male</label>
                            <label><input type="radio" name="gender" value="Female"> Female</label>
                            <label><input type="radio" name="gender" value="Other"> Other</label>
                        </div>
                        <span id="genderError" class="error-text"></span>
                    </div>

                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="terms" name="terms">
                            I agree to the Terms & Conditions and Privacy Policy *
                        </label>
                        <span id="termsError" class="error-text"></span>
                    </div>

                    <!-- Security Verification (CAPTCHA) -->
                    <div class="form-group">
                        <label for="captcha_input">Security Verification *</label>
                        <div class="captcha-row">
                            <div id="captchaDisplay" class="captcha-box">U55j4Y</div>
                            <button type="button" id="refreshCaptcha" class="btn-refresh">Refresh</button>
                        </div>
                        <input type="hidden" id="captcha_generated" name="captcha_generated" value="">
                        <input type="text" id="captcha_input" name="captcha_input" placeholder="Enter characters shown above" required>
                        <span id="captchaError" class="error-text"></span>
                    </div>

                    <button type="submit" class="btn">Create Account</button>
                </form>

                <p style="margin-top: 20px; text-align: center; font-size: 0.9rem;">
                    Already have an account? <a href="login.php" style="text-decoration: underline; font-weight: 600;">Login here</a>
                </p>
            </div>
        </main>
    </div>

    <script src="JS/script.js"></script>
    <script>
        // Practical 9: Frontend Validation & Security Verification (CAPTCHA)
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("prac9Form");
            const captchaDisplay = document.getElementById("captchaDisplay");
            const captchaHidden = document.getElementById("captcha_generated");
            const refreshBtn = document.getElementById("refreshCaptcha");

            function generateCaptcha() {
                const chars = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789";
                let code = "";
                for (let i = 0; i < 6; i++) {
                    code += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                captchaDisplay.textContent = code;
                captchaHidden.value = code;
            }

            generateCaptcha();
            refreshBtn.addEventListener("click", generateCaptcha);

            // Password Strength Meter
            const passEl = document.getElementById("password");
            const strengthEl = document.getElementById("passwordStrength");
            passEl.addEventListener("input", function() {
                const val = passEl.value;
                let s = 0;
                if (val.length >= 8) s++;
                if (/[A-Z]/.test(val) && /[a-z]/.test(val)) s++;
                if (/[0-9]/.test(val)) s++;
                if (/[^A-Za-z0-9]/.test(val)) s++;

                strengthEl.className = "password-strength";
                if (!val) strengthEl.textContent = "";
                else if (s <= 2) { strengthEl.textContent = "Password Strength: Weak"; strengthEl.classList.add("strength-weak"); }
                else if (s === 3) { strengthEl.textContent = "Password Strength: Medium"; strengthEl.classList.add("strength-medium"); }
                else { strengthEl.textContent = "Password Strength: Strong"; strengthEl.classList.add("strength-strong"); }
            });

            // Frontend Submit Check (Only blocks submission if invalid; allows POST to PHP if valid)
            form.addEventListener("submit", function(e) {
                let valid = true;

                const showErr = (id, errId, condition, msg) => {
                    const el = document.getElementById(id);
                    const err = document.getElementById(errId);
                    if (!condition) {
                        err.textContent = msg;
                        if (el) el.classList.add("input-error");
                        valid = false;
                    } else {
                        err.textContent = "";
                        if (el) el.classList.remove("input-error");
                    }
                };

                const nameVal = document.getElementById("name").value.trim();
                const emailVal = document.getElementById("email").value.trim();
                const mobileVal = document.getElementById("mobile").value.trim();
                const passVal = passEl.value;
                const confVal = document.getElementById("confirm_password").value;
                const courseVal = document.getElementById("course").value;
                const yearVal = document.getElementById("year").value;
                const genderChecked = document.querySelector('input[name="gender"]:checked');
                const termsChecked = document.getElementById("terms").checked;
                const capIn = document.getElementById("captcha_input").value.trim();

                showErr("name", "nameError", /^[a-zA-Z\s]{3,}$/.test(nameVal), "Full name is required (min 3 letters).");
                showErr("email", "emailError", /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal), "Valid email address is required.");
                showErr("mobile", "mobileError", /^[0-9]{10}$/.test(mobileVal), "Enter a valid 10-digit mobile number.");
                showErr("password", "passwordError", passVal.length >= 8, "Password must be at least 8 characters.");
                showErr("confirm_password", "confirmError", confVal !== "" && confVal === passVal, "Passwords do not match.");
                showErr("course", "courseError", courseVal !== "", "Please select your course.");
                showErr("year", "yearError", yearVal !== "", "Please select your year of study.");
                showErr(null, "genderError", genderChecked !== null, "Please select your gender.");
                showErr(null, "termsError", termsChecked, "You must agree to the Terms & Conditions.");
                showErr("captcha_input", "captchaError", capIn === captchaHidden.value, "Characters do not match the security code.");

                if (!valid) {
                    e.preventDefault(); // Stop submission only if frontend validation fails
                }
            });
        });
    </script>
</body>
</html>