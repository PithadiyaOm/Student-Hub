<?php
require_once '../config/database.php';

$message = "";
$messageType = "";
$editMode = false;
$editData = ['id' => '', 'full_name' => '', 'email' => '', 'mobile' => '', 'course' => '', 'year_of_study' => ''];

// 1. Handle Create / Update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $id = $_POST["id"] ?? "";
    $name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $course = trim($_POST["course"] ?? "");
    $year = trim($_POST["year_of_study"] ?? "");

    if ($action === "add") {
        $stmt = $conn->prepare("INSERT INTO practical_students (full_name, email, mobile, course, year_of_study, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("sssss", $name, $email, $mobile, $course, $year);
        if ($stmt->execute()) {
            $message = "Student added successfully!";
            $messageType = "success-alert";
        } else {
            $message = "Error: Email might already exist.";
            $messageType = "error-alert";
        }
        $stmt->close();
    } elseif ($action === "update" && !empty($id)) {
        $stmt = $conn->prepare("UPDATE practical_students SET full_name=?, email=?, mobile=?, course=?, year_of_study=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $mobile, $course, $year, $id);
        if ($stmt->execute()) {
            $message = "Student updated successfully!";
            $messageType = "success-alert";
        } else {
            $message = "Error updating student record.";
            $messageType = "error-alert";
        }
        $stmt->close();
    }
}

// 2. Handle Soft Delete
if (isset($_GET["delete_id"])) {
    $delId = intval($_GET["delete_id"]);
    $stmt = $conn->prepare("UPDATE practical_students SET status = 'inactive' WHERE id = ?");
    $stmt->bind_param("i", $delId);
    if ($stmt->execute()) {
        $message = "Student status changed to inactive (Soft Deleted).";
        $messageType = "success-alert";
    }
    $stmt->close();
}

// 3. Handle Edit Fetch
if (isset($_GET["edit_id"])) {
    $editMode = true;
    $editId = intval($_GET["edit_id"]);
    $stmt = $conn->prepare("SELECT * FROM practical_students WHERE id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $editData = $row;
    }
    $stmt->close();
}

// 4. Search & Filter Parameters
$search = trim($_GET["search"] ?? "");
$filterCourse = trim($_GET["filter_course"] ?? "All");

$query = "SELECT * FROM practical_students WHERE status = 'active'";
$params = [];
$types = "";

if ($search !== "") {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR course LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

if ($filterCourse !== "All") {
    $query .= " AND course = ?";
    $params[] = $filterCourse;
    $types .= "s";
}

$query .= " ORDER BY id DESC";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Student Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .success-alert { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .error-alert { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        .admin-table th, .admin-table td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); text-align: left; }
        .admin-table th { background-color: var(--bg-body); color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .action-btns a { padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; text-decoration: none; font-weight: bold; margin-right: 5px; }
        .btn-edit { background-color: #ffc107; color: #000; }
        .btn-delete { background-color: #dc3545; color: #fff; }
        .controls-row { display: grid; grid-template-columns: 2fr 1fr auto auto; gap: 15px; align-items: end; margin-bottom: 25px; }
    </style>
</head>
<body>
    <div class="layout">
        <aside id="sidebar">
            <div class="logo"><h2>STUDENTHUB</h2><p>Admin Portal</p></div>
            <nav>
                <a href="../admin.php">Admin Panel</a>
                <a href="student-management.php" class="active">Student Management</a>
                <a href="../dashboard.php">Dashboard</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </aside>

        <main>
            <h1>Manage Students</h1>
            <p class="subtitle">Add, edit, search, filter, and manage student records securely using MySQLi[cite: 13, 14].</p>

            <?php if ($message !== ""): ?>
                <div class="<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Add / Edit Form Card -->
            <div class="card" style="margin-bottom: 30px;">
                <h3><?php echo $editMode ? "Edit Student Record" : "Add New Student"; ?></h3>
                <form action="student-management.php" method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="action" value="<?php echo $editMode ? "update" : "add"; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($editData['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($editData['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number *</label>
                            <input type="tel" name="mobile" value="<?php echo htmlspecialchars($editData['mobile']); ?>" maxlength="10" required>
                        </div>
                        <div class="form-group">
                            <label>Course *</label>
                            <select name="course" required>
                                <option value="">--Select Course--</option>
                                <option value="Computer Engineering" <?php if($editData['course']=='Computer Engineering') echo 'selected'; ?>>Computer Engineering</option>
                                <option value="Information Technology" <?php if($editData['course']=='Information Technology') echo 'selected'; ?>>Information Technology</option>
                                <option value="Artificial Intelligence" <?php if($editData['course']=='Artificial Intelligence') echo 'selected'; ?>>Artificial Intelligence</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Year of Study *</label>
                            <select name="year_of_study" required>
                                <option value="">--Select Year--</option>
                                <option value="Year 1" <?php if($editData['year_of_study']=='Year 1') echo 'selected'; ?>>Year 1</option>
                                <option value="Year 2" <?php if($editData['year_of_study']=='Year 2') echo 'selected'; ?>>Year 2</option>
                                <option value="Year 3" <?php if($editData['year_of_study']=='Year 3') echo 'selected'; ?>>Year 3</option>
                                <option value="Year 4" <?php if($editData['year_of_study']=='Year 4') echo 'selected'; ?>>Year 4</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn" style="width: auto; margin-top: 10px;"><?php echo $editMode ? "Update Student" : "Add Student"; ?></button>
                    <?php if ($editMode): ?>
                        <a href="student-management.php" style="margin-left: 15px; text-decoration: underline; font-weight: 600;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Search, Filter & Export Bar -->
            <div class="card">
                <form method="GET" action="student-management.php" class="controls-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Search Student</label>
                        <input type="text" name="search" placeholder="Search by name, email or course..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Filter by Course</label>
                        <select name="filter_course">
                            <option value="All">All Courses</option>
                            <option value="Computer Engineering" <?php if($filterCourse=='Computer Engineering') echo 'selected'; ?>>Computer Engineering</option>
                            <option value="Information Technology" <?php if($filterCourse=='Information Technology') echo 'selected'; ?>>Information Technology</option>
                            <option value="Artificial Intelligence" <?php if($filterCourse=='Artificial Intelligence') echo 'selected'; ?>>Artificial Intelligence</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn" style="height: 42px;">Apply</button>
                    </div>
                    <div>
                        <a href="export-students.php" class="btn" style="height: 42px; display: inline-flex; align-items: center; background-color: #28a745; text-decoration: none;">Export CSV</a>
                    </div>
                </form>

                <!-- Student Records Table -->
                <div style="overflow-x: auto; margin-top: 20px;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows === 0): ?>
                                <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 20px;">No student records found.</td></tr>
                            <?php else: ?>
                                <?php while ($student = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $student['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($student['full_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                                        <td><span class="tag academic"><?php echo htmlspecialchars($student['course']); ?></span></td>
                                        <td><?php echo htmlspecialchars($student['year_of_study']); ?></td>
                                        <td class="action-btns">
                                            <a href="student-management.php?edit_id=<?php echo $student['id']; ?>" class="btn-edit">Edit</a>
                                            <a href="student-management.php?delete_id=<?php echo $student['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to deactivate (soft delete) this student?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>