<?php
// register.php
include 'db_connect.php';

// 1. Prevent direct access
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

echo "Backend received POST request successfully!<br>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// 3. Validate input
if (
    !isset($_POST['username']) ||
    !isset($_POST['email']) ||
    !isset($_POST['password']) ||
    empty($_POST['username']) ||
    empty($_POST['email']) ||
    empty($_POST['password'])
) {
    die("All fields are required");
}

$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];

// 4. Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// 5. Insert user using prepared statement
$sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $email, $hash);

if ($stmt->execute()) {
    echo "<br>Registration successful!";
} else {
    echo "<br>Registration failed: " . $stmt->error;
}
?>
