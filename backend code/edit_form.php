<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM diary_entries WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Entry not found");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Diary Entry</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container {
            width: 500px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover { background-color: #45a049; }
        a { display: inline-block; margin-top: 10px; color: #333; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Diary Entry</h2>
    <form action="edit_entry.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

        <label>Title:</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required><br>

        <label>Content:</label><br>
        <textarea name="content" rows="8" required><?php echo htmlspecialchars($row['content']); ?></textarea><br>

        <button type="submit">Update</button>
    </form>
    <a href="view_entries.php">← Back to Diary</a>
</div>
</body>
</html>