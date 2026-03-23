<?php
/**
 * Database Connection for Dream-Notebook-Project
 * This file connects your PHP frontend to the XAMPP MySQL/MariaDB database.
 */

// 1. Database Configuration
// If you are using XAMPP, the default user is 'root' and password is empty ''
$host = "localhost";
$db_user = "root";
$db_pass = ""; // Leave empty for XAMPP default
$db_name = "dream_notebook";
$port = 3306; // Use 3307 if you changed your XAMPP port earlier

// 2. Create Connection
$conn = new mysqli($host, $db_user, $db_pass, $db_name, $port);

// 3. Check Connection
if ($conn->connect_error) {
    // If connection fails, stop and show the error
    die("Database Connection Failed: " . $conn->connect_error);
}

// 4. Set Character Set to UTF-8 (important for dream text with emojis)
$conn->set_charset("utf8mb4");

// Note: We do not close the connection here so other files can use $conn
?>