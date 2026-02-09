<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

echo "Backend running (Register)<br>";
print_r($_POST);

if (
    empty($_POST['username']) ||
    empty($_POST['email']) ||
    empty($_POST['password'])
) {
    die("All fields are required");
}

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, email, password_hash)
        VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $email, $hash);

if ($stmt->execute()) {
    echo "<br>Registration successful";
    // header("Location: login.html");
} else {
    echo "<br>Registration failed";
}
?>
