<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Invalid request");
}

if (!isset($_POST['id']) || !isset($_POST['title']) || !isset($_POST['content'])) {
    die("Missing data");
}

$id = $_POST['id'];
$title = $_POST['title'];
$content = $_POST['content'];
$user_id = $_SESSION['user_id'];

$sql = "UPDATE diary_entries
        SET title = ?, content = ?
        WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $title, $content, $id, $user_id);

if ($stmt->execute()) {
    header("Location: view_entries.php");
    exit();
} else {
    echo "Update failed";
}
?>