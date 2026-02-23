<?php

$conn = new mysqli("localhost", "root", "", "dream_notebook");

if ($conn->connect_error) {
    die("Database connection failed");
}
?>

