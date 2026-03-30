<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if (!isset($_POST['del']) || !is_numeric($_POST['del'])) {
    die("Invalid request");
}

$entry_id = (int) $_POST['del'];
$user_id  = $_SESSION['user_id'];

$sql = "DELETE FROM diary_entries WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $entry_id, $user_id);

if ($stmt->execute()) {
    header("Location: history.php");
    exit;
} else {
    die("Failed to delete entry");
}
?>