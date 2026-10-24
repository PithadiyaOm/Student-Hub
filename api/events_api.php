<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$response = ['status' => false, 'message' => 'Invalid request method.'];

// Handle GET Request (List Events)
if ($method === 'GET') {
    $result = $conn->query("SELECT * FROM events_management ORDER BY event_date DESC");
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    echo json_encode([
        'status' => true,
        'message' => 'Events fetched successfully. Total: ' . count($events),
        'data' => $events
    ]);
    exit();
}

// Handle POST Requests (Add, Update, Delete)
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($input['title'] ?? $_POST['title'] ?? '');
        $category = trim($input['category'] ?? $_POST['category'] ?? '');
        $date = $input['event_date'] ?? $_POST['event_date'] ?? '';
        $desc = trim($input['description'] ?? $_POST['description'] ?? '');
        $status = $input['status'] ?? $_POST['status'] ?? 'Open';

        // Input Validation
        if (empty($title) || empty($category) || empty($date) || empty($desc)) {
            echo json_encode(['status' => false, 'message' => 'All required fields must be filled.']);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO events_management (title, category, event_date, description, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $category, $date, $desc, $status);
        if ($stmt->execute()) {
            echo json_encode(['status' => true, 'message' => 'Event added successfully.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Database error: Unable to add event.']);
        }
        $stmt->close();
    } elseif ($action === 'update') {
        $id = intval($input['event_id'] ?? $_POST['event_id'] ?? 0);
        $title = trim($input['title'] ?? $_POST['title'] ?? '');
        $category = trim($input['category'] ?? $_POST['category'] ?? '');
        $date = $input['event_date'] ?? $_POST['event_date'] ?? '';
        $desc = trim($input['description'] ?? $_POST['description'] ?? '');
        $status = $input['status'] ?? $_POST['status'] ?? 'Open';

        if ($id <= 0 || empty($title) || empty($category) || empty($date)) {
            echo json_encode(['status' => false, 'message' => 'Invalid update parameters.']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE events_management SET title=?, category=?, event_date=?, description=?, status=? WHERE event_id=?");
        $stmt->bind_param("sssssi", $title, $category, $date, $desc, $status, $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => true, 'message' => 'Event updated successfully.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Database error: Unable to update event.']);
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        $id = intval($input['event_id'] ?? $_POST['event_id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['status' => false, 'message' => 'Invalid event ID.']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM events_management WHERE event_id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => true, 'message' => 'Event deleted successfully.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Database error: Unable to delete event.']);
        }
        $stmt->close();
    }
    exit();
}
?>
