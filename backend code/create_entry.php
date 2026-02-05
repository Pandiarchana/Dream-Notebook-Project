<?php
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$content = $_POST['content'];
$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO diary_entries (user_id, content)
        VALUES ('$user_id', '$content')";

$conn->query($sql);
echo "Entry saved";
?>
