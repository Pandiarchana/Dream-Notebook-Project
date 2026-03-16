<?php
session_start();
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

// 1. MATCHING TABLE NAME: Using 'dream_entries' as seen in your Workbench screenshot
$sql = "INSERT INTO dream_entries (user_id, title, content) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $_SESSION['user_id'], $title, $content);

if ($stmt->execute()) {
    // 2. GET THE NEW ID: Get the ID of the dream we just saved
    $dream_id = $conn->insert_id;

    // 3. TRIGGER AI: Run the Python script in the background
    // We use escapeshellarg to make sure the dream text doesn't break the command line
    $command = "python ../analyze_dream.py " . $dream_id . " " . escapeshellarg($content);

    // Execute the command (this sends the data to Gemini)
    shell_exec($command);

    // 4. REDIRECT: Go back to the view page
    header("Location: view_entries.php");
    exit;
} else {
    die("Failed to save entry: " . $conn->error);
}
?>