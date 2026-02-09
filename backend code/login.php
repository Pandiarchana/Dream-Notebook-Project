<?php
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

echo "Backend running (Create Entry)<br>";
print_r($_POST);

if (empty($_POST['title']) || empty($_POST['content'])) {
    die("Entry cannot be empty");
}

$title = $_POST['title'];
$content = $_POST['content'];
$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO diary_entries (user_id, title, content)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $title, $content);

if ($stmt->execute()) {
    echo "<br>Entry saved successfully";
    // header("Location: view_diary.php");
} else {
    echo "<br>Failed to save entry";
}
?>
