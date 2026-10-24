<?php
require_once '../config/database.php';

$message = "";
$messageType = "";
$editMode = false;
$editData = [
    'event_id' => '',
    'title' => '',
    'category' => '',
    'event_date' => '',
    'description' => '',
    'status' => 'Open',
    'poster' => ''
];

// Ensure uploads directory exists
$uploadDir = "../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 1. Handle Add / Update Event Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $eventId = $_POST["event_id"] ?? "";
    $title = trim($_POST["title"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $eventDate = $_POST["event_date"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $status = $_POST["status"] ?? "Open";
    $currentPoster = $_POST["current_poster"] ?? "";

    $posterName = $currentPoster;

    // File Upload Handling & Validation (Type & Size max 2MB)
    if (isset($_FILES["poster"]) && $_FILES["poster"]["error"] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES["poster"]["tmp_name"];
        $fileName = $_FILES["poster"]["name"];
        $fileSize = $_FILES["poster"]["size"];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $maxSize = 2 * 1024 * 1024; // 2 MB

        if (!in_array($fileExtension, $allowedExtensions)) {
            $message = "Invalid file type. Only JPG, PNG, and WEBP files are allowed.";
            $messageType = "error-alert";
        } elseif ($fileSize > $maxSize) {
            $message = "File size exceeds the 2 MB limit.";
            $messageType = "error-alert";
        } else {
            $newFileName = uniqid('poster_') . '.' . $fileExtension;
            $destPath = $uploadDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $posterName = $newFileName;
            }
        }
    }

    if ($messageType !== "error-alert") {
        if ($action === "add") {
            $stmt = $conn->prepare("INSERT INTO events_management (title, category, event_date, description, status, poster) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $title, $category, $eventDate, $description, $status, $posterName);
            if ($stmt->execute()) {
                $message = "Event added successfully.";
                $messageType = "success-alert";
            } else {
                $message = "Database error: Unable to add event.";
                $messageType = "error-alert";
            }
            $stmt->close();
        } elseif ($action === "update" && !empty($eventId)) {
            $stmt = $conn->prepare("UPDATE events_management SET title=?, category=?, event_date=?, description=?, status=?, poster=? WHERE event_id=?");
            $stmt->bind_param("ssssssi", $title, $category, $eventDate, $description, $status, $posterName, $eventId);
            if ($stmt->execute()) {
                $message = "Event updated successfully.";
                $messageType = "success-alert";
            } else {
                $message = "Database error: Unable to update event.";
                $messageType = "error-alert";
            }
            $stmt->close();
        }
    }
}

// 2. Handle Delete Event
if (isset($_GET["delete_id"])) {
    $delId = intval($_GET["delete_id"]);
    $stmt = $conn->prepare("DELETE FROM events_management WHERE event_id = ?");
    $stmt->bind_param("i", $delId);
    if ($stmt->execute()) {
        $message = "Event deleted permanently.";
        $messageType = "success-alert";
    }
    $stmt->close();
}

// 3. Handle Edit Mode Data Fetching
if (isset($_GET["edit_id"])) {
    $editMode = true;
    $editId = intval($_GET["edit_id"]);
    $stmt = $conn->prepare("SELECT * FROM events_management WHERE event_id = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $editData = $row;
    }
    $stmt->close();
}

// 4. Search and Filter Parameters[cite: 14, 15]
$search = trim($_GET["search"] ?? "");
$filterCategory = trim($_GET["filter_category"] ?? "All");
$filterStatus = trim($_GET["filter_status"] ?? "All");

$query = "SELECT * FROM events_management WHERE 1=1";
$params = [];
$types = "";

if ($search !== "") {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($filterCategory !== "All") {
    $query .= " AND category = ?";
    $params[] = $filterCategory;
    $types .= "s";
}

if ($filterStatus !== "All") {
    $query .= " AND status = ?";
    $params[] = $filterStatus;
    $types .= "s";
}

$query .= " ORDER BY event_date DESC";
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
    <title>StudentHub - Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .success-alert { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .error-alert { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        .admin-table th, .admin-table td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); text-align: left; vertical-align: middle; }
        .admin-table th { background-color: var(--bg-body); color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .action-btns a { padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; text-decoration: none; font-weight: bold; margin-right: 5px; }
        .btn-edit { background-color: #007bff; color: #fff; }
        .btn-delete { background-color: #dc3545; color: #fff; }
        .controls-row { display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 15px; align-items: end; margin-bottom: 25px; }
        .poster-thumb { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color); }
    </style>
</head>
<body>
    <div class="layout">
        <aside id="sidebar">
            <div class="logo"><h2>STUDENTHUB</h2><p>Admin Portal</p></div>
            <nav>
                <a href="../admin.php">Admin Panel</a>
                <a href="student-management.php">Student Management</a>
                <a href="admin-events.php" class="active">Event Management</a>
                <a href="../dashboard.php">Dashboard</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </aside>

        <main>
            <h1>Event Management</h1>
            <p class="subtitle">Add, view, edit and manage StudentHub events[cite: 14].</p>

            <?php if ($message !== ""): ?>
                <div class="<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Add / Update Event Form Card[cite: 14] -->
            <div class="card" style="margin-bottom: 30px;">
                <h3><?php echo $editMode ? "Update Event" : "Add New Event"; ?></h3>
                <form action="admin-events.php" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
                    <input type="hidden" name="action" value="<?php echo $editMode ? "update" : "add"; ?>">
                    <?php if ($editMode): ?>
                        <input type="hidden" name="event_id" value="<?php echo $editData['event_id']; ?>">
                        <input type="hidden" name="current_poster" value="<?php echo htmlspecialchars($editData['poster']); ?>">
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Event Title *</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($editData['title']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Category *</label>
                            <input type="text" name="category" placeholder="e.g. Workshop, Festival, Sports" value="<?php echo htmlspecialchars($editData['category']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Event Date *</label>
                            <input type="date" name="event_date" value="<?php echo htmlspecialchars($editData['event_date']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select name="status" required>
                                <option value="Open" <?php if($editData['status']=='Open') echo 'selected'; ?>>Open</option>
                                <option value="Closed" <?php if($editData['status']=='Closed') echo 'selected'; ?>>Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" rows="5" required><?php echo htmlspecialchars($editData['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Poster (JPG, PNG or WEBP; maximum 2 MB)[cite: 14]</label>
                        <input type="file" name="poster" accept=".jpg, .jpeg, .png, .webp">
                        <?php if ($editMode && !empty($editData['poster'])): ?>
                            <p style="margin-top: 8px; font-size: 0.85rem;">Current poster: <a href="../uploads/<?php echo htmlspecialchars($editData['poster']); ?>" target="_blank">View</a></p>
                            <p class="text-muted" style="font-size: 0.8rem;">Leave the poster field empty to keep the current poster[cite: 17].</p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn" style="width: auto; margin-top: 10px;"><?php echo $editMode ? "Update Event" : "Add Event"; ?></button>
                    <?php if ($editMode): ?>
                        <a href="admin-events.php" style="margin-left: 15px; text-decoration: underline; font-weight: 600;">Cancel edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Search and Filter Bar[cite: 14, 15] -->
            <div class="card">
                <form method="GET" action="admin-events.php" class="controls-row">
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Search</label>
                        <input type="text" name="search" placeholder="Search title or description..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Category</label>
                        <select name="filter_category">
                            <option value="All">All categories</option>
                            <option value="Workshop" <?php if($filterCategory=='Workshop') echo 'selected'; ?>>Workshop</option>
                            <option value="Festival" <?php if($filterCategory=='Festival') echo 'selected'; ?>>Festival</option>
                            <option value="Sports" <?php if($filterCategory=='Sports') echo 'selected'; ?>>Sports</option>
                            <option value="Academic" <?php if($filterCategory=='Academic') echo 'selected'; ?>>Academic</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label>Status</label>
                        <select name="filter_status">
                            <option value="All">All statuses</option>
                            <option value="Open" <?php if($filterStatus=='Open') echo 'selected'; ?>>Open</option>
                            <option value="Closed" <?php if($filterStatus=='Closed') echo 'selected'; ?>>Closed</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn" style="height: 42px;">Search / Filter</button>
                    </div>
                    <div>
                        <a href="admin-events.php" class="btn" style="height: 42px; display: inline-flex; align-items: center; background-color: #6c757d; text-decoration: none;">Clear</a>
                    </div>
                </form>

                <!-- Event List Table[cite: 14] -->
                <div style="overflow-x: auto; margin-top: 20px;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Poster</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows === 0): ?>
                                <tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">No events found.</td></tr>
                            <?php else: ?>
                                <?php while ($event = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($event['poster']) && file_exists($uploadDir . $event['poster'])): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($event['poster']); ?>" class="poster-thumb" alt="Poster">
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);">No poster</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                        <td><span class="tag event"><?php echo htmlspecialchars($event['category']); ?></span></td>
                                        <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                                        <td>
                                            <span class="tag" style="background: <?php echo $event['status']=='Open' ? 'rgba(40,167,69,0.15); color:#28a745;' : 'rgba(220,53,69,0.15); color:#dc3545;'; ?>">
                                                <?php echo htmlspecialchars($event['status']); ?>
                                            </span>
                                        </td>
                                        <td class="action-btns">
                                            <a href="admin-events.php?edit_id=<?php echo $event['event_id']; ?>" class="btn-edit">Edit</a>
                                            <a href="admin-events.php?delete_id=<?php echo $event['event_id']; ?>" class="btn-delete" onclick="return confirm('Delete this event permanently?');">Delete</a>
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