<?php
// login.php
include 'db_connect.php';

// 1. Prevent direct access
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

// 2. Debug
echo "Backend received POST request successfully!<br>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// 3. Check required fields
if (!isset($_POST['email']) || !isset($_POST['password']) || empty($_POST['email']) || empty($_POST['password'])) {
    die("Please fill in all fields");
}

$email = $_POST['email'];
$password = $_POST['password'];

// 4. Get user by email
$sql = "SELECT id, password_hash FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 5. Verify password
if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    echo "<br>Login successful! User ID: " . $user['id'];
    // header("Location: home.php"); // 测试阶段可以先注释掉跳转
    exit();
} else {
    echo "<br>Invalid email or password";
}
?>
