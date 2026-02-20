<?php
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

$title   = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';

if ($title === '' || $content === '') {
    die("Entry cannot be empty");
}

$sql = "INSERT INTO diary_entries (user_id, title, content)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $_SESSION['user_id'], $title, $content);

if ($stmt->execute()) {
    header("Location: view_entries.php");
    exit;
} else {
    die("Failed to save entry");
}