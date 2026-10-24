<?php
require_once 'db.php';

// Prepared Statement implementation to retrieve test data securely
$stmt = $pdo->prepare("SELECT * FROM Students WHERE course = :course");
$stmt->execute(['course' => 'Computer Engineering']);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub Database Connection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }
        h2 {
            color: #111;
            margin-bottom: 10px;
        }
        .success-msg {
            color: green;
            font-size: 1rem;
        }
    </style>
</head>
<body>

    <h2>StudentHub Database Connection</h2>
    <p class="success-msg">Database connected successfully!</p>

</body>
</html>