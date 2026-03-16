<?php
// Database configuration for MySQL Workbench
$host = "localhost";
$port = "3306"; // Default port for MySQL Workbench
$user = "root";
$pass = "Archana@27";
$db   = "dream_notebook";

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = new mysqli($host, $user, $pass, $db, $port);

    // Set charset to utf8mb4 (good for dreams/emojis)
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // If connection fails, show the specific error
    die("Database connection failed: " . $e->getMessage());
}

// Success! If the code reaches here, your DB is connected.
?>