<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="dream_notebook_backup.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Content', 'Mood', 'Created At']);


$sql = "SELECT id, content, mood, created_at FROM diary_entries WHERE user_id = ? 
ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>