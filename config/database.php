<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "studenthub";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>
