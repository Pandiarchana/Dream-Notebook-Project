<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Unauthorized");
}

$login    = $_POST['login'] ?? '';
$password = $_POST['password'] ?? '';

if ($login === '' || $password === '') {
    die("Unauthorized");
}

$sql = "SELECT * FROM users WHERE email = ? OR username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $login, $login);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Unauthorized");
}

if (!password_verify($password, $user['password_hash'])) {
    die("Unauthorized");
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

header("Location: view_entries.php");
exit;
