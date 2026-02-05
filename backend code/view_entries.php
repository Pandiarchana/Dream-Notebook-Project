<?php
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

$result = $conn->query(
  "SELECT * FROM diary_entries WHERE user_id='$user_id'"
);

while ($row = $result->fetch_assoc()) {
    echo "<p>" . $row['content'] . "</p>";
}
?>
