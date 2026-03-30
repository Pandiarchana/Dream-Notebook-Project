<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

$user_id = $_SESSION['user_id'];

$result = $conn->query(
    "SELECT id, title, content, created_at 
     FROM diary_entries 
     WHERE user_id = '$user_id'
     ORDER BY created_at DESC"
);

$entries = [];
while ($row = $result->fetch_assoc()) {
    $entries[] = $row;
}

$payload = json_encode($entries);
$command = escapeshellcmd("python analyze_emotion.py '$payload'");
$output = shell_exec($command);

header('Content-Type: application/json');
echo $output;
?>