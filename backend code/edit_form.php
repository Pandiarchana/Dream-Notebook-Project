<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$id = $_POST['id'] ?? '';
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($id) || empty($title) || empty($content)) {
    die("All fields are required");
}

$sql = "UPDATE diary_entries
        SET title = ?, content = ?
        WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $title, $content, $id, $user_id);

if ($stmt->execute()) {
    echo "<script>
            alert('Entry updated successfully!');
            window.location.href='view_entries.php';
          </script>";
    exit;
} else {
    die("Update failed");
}
?>