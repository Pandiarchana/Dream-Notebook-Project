<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$result = $conn->query(
    "SELECT * FROM diary_entries
     WHERE user_id = '$user_id'
     ORDER BY created_at DESC"
);

echo "<h2>Your Diary Entries</h2>";

while ($row = $result->fetch_assoc()) {
    echo "<h3>{$row['title']}</h3>";
    echo "<small>{$row['created_at']}</small><br>";
    echo "<p>{$row['content']}</p>";
    echo "<a href='edit_form.php?id={$row['id']}'>Edit</a> | ";
    echo "<a href='delete_entry.php?id={$row['id']}'>Delete</a>";
    echo "<hr>";
}
?>
