<?php
include("dbconnect.php");
session_start();



if(isset($_POST['sub'])){
    $title = trim($_POST['title']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $description = trim($_POST['description']);

    if(!empty($title) && !empty($date) && !empty($time) && !empty($description)){

        $stmt = $connect->prepare("INSERT INTO entry(title, date, time, description) VALUES(?,?,?,?)");
        $stmt->bind_param("ssss", $title, $date, $time, $description);

        if($stmt->execute()){
            echo "<script>alert('Entry saved successfully!');</script>";
        } else {
            echo "<script>alert('Error saving entry');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dream Diary</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style type="text/css">

    * {
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  margin: 0;
  padding: 0;
}

body {
  background: #f0f2f8;
  color: #333;
}

.app {
  display: flex;
  height: 100vh;
}

/* Sidebar */
.sidebar {
  width: 220px;
  background: #fff;
  border-right: 1px solid #ddd;
  padding: 20px;
  display: flex;
  flex-direction: column;
}

.user {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  margin-bottom: 30px;
}

.avatar {
  width: 40px;
  height: 40px;
  background: #5a67d8;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.add-task {
  background: #5a67d8;
  color: white;
  border: none;
  padding: 10px;
  border-radius: 6px;
  cursor: pointer;
  margin-bottom: 20px;
  transition: background 0.3s;
}

.add-task:hover {
  background: #434aa8;
}

.menu a {
  display: block;
  padding: 10px;
  border-radius: 6px;
  text-decoration: none;
  color: #333;
  margin-bottom: 8px;
  transition: background 0.3s;
}

.menu a.active, .menu a:hover {
  background: #e0e4ff;
}

footer {
  margin-top: auto;
  font-size: 13px;
}

footer a {
  display: block;
  margin-top: 10px;
  color: #555;
  text-decoration: none;
}

footer a:hover {
  text-decoration: underline;
}

/* Mainn Sect */
.main-section {
  flex: 1;
  padding: 40px;
  overflow-y: auto;
}

.entry-form {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  margin-bottom: 40px;
}

.entry-form h1 {
  margin-bottom: 10px;
  color: #5a67d8;
}

.entry-form p {
  margin-bottom: 20px;
  color: #666;
}

.input-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 20px;
}

.input-group label {
  margin-bottom: 6px;
  font-weight: 500;
}

.input-group input,
.input-group textarea {
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 14px;
  resize: vertical;
}

.input-group textarea {
  min-height: 100px;
}

.save-btn {
  background: #5a67d8;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
}

.save-btn:hover {
  background: #434aa8;
}


/* Entries Section */
.entries h2 {
  margin-bottom: 20px;
  color: #5a67d8;
}

.entry-item {
  background: #fff;
  padding: 16px;
  border-radius: 10px;
  margin-bottom: 16px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.05);
}

.entry-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  font-size: 13px;
  color: #555;
}

.entry-header i {
  margin-right: 4px;
}






.entry-container {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); /* Grid is better than column flex */
  gap: 25px;
  padding: 20px 0;
  width: 100%;
}

.entry-card {
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 10px 25px rgba(90, 103, 216, 0.1);
  overflow: hidden;
  transition: all 0.3s ease;
  border: 1px solid rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
}

.entry-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 30px rgba(90, 103, 216, 0.15);
}

.entry-header {
  background: linear-gradient(135deg, #5a67d8 0%, #434aa8 100%);
  padding: 15px;
  text-align: left;
}

.entry-header h2 {
  color: #ffffff;
  font-size: 1.2rem;
  margin: 0;
  text-transform: capitalize;
}

.entry-content {
  padding: 20px;
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.entry-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #718096;
  margin-bottom: 15px;
  padding-bottom: 10px;
  border-bottom: 1px dashed #e2e8f0;
}

.entry-meta span i {
  margin-right: 5px;
  color: #5a67d8;
}

.entry-description {
  font-size: 0.95rem;
  color: #4a5568;
  line-height: 1.6;
  margin-bottom: 20px;
  font-style: italic;
  display: -webkit-box;
  -webkit-line-clamp: 4; /* Limits text to 4 lines */
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn-group {
  display: flex;
  gap: 10px;
  margin-top: auto;
}

.btn-group button {
  flex: 1;
  padding: 10px;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
  border: none;
}

.btn-delete {
  background: #fff;
  color: #e53e3e;
  border: 1px solid #e53e3e !important;
}

.btn-delete:hover {
  background: #fff5f5;
}

.btn-update {
  background: #5a67d8;
  color: white;
}

.btn-update:hover {
  background: #434aa8;
}

  </style>

</head>

<body>

<div class="app">

<!-- Sidebar -->
<aside class="sidebar">
  <div class="user">
    <div class="avatar">U</div>
    <span>User</span>
  </div>

  <nav class="menu">
    <a href="#">Welcome</a>
    <a href="#">Create</a>
    <a href="#">Entries</a>
    <a href="Homepage.html">Logout</a>
  </nav>
</aside>

<!-- Main -->
<section class="main-section">

<!-- FORM -->
<form method="POST" class="entry-form">

  <h2>Dream Diary</h2>

  <div class="input-group">
    <label>Title</label>
    <input type="text" name="title" required>
  </div>

  <div class="input-group">
    <label>Date</label>
    <input type="date" name="date" required>
  </div>

  <div class="input-group">
    <label>Time</label>
    <input type="time" name="time" required>
  </div>

  <div class="input-group">
    <label>Your Dream</label>
    <textarea name="description" required></textarea>
  </div>

  <button type="submit" name="sub" class="save-btn">Save Entry</button>

</form>

<!-- DISPLAY ENTRIES -->
<h2>Your Entries</h2>

<div class="entry-container">
<?php
$query ="SELECT * FROM entry";
$result = mysqli_query($connect,$query);

while ($record = mysqli_fetch_array($result)) {

    print("<form action='Delete.php' method='POST' class='entry-card'>");

    print("<div class='entry-header'>");
    print("<h2>$record[title]</h2>");
    print("</div>");

    print("<div class='entry-details'>");
    print("<p>Date - $record[date]</p>");
    print("<h3>Time - $record[time]</h3>");
    print("<h3>Description - $record[description]</h3>");

    print("<div class='btn-group'>");
    print("<button type='submit' name='del' value='$record[eid]'
            onclick='return confirm(\"Do you want to delete?\")'>Delete</button>");
    print("<button type='submit' name='update' value='$record[eid]'>Update</button>");
    print("</div>");
    print("</div>");

    print("</div>");

    print("</form>");
}
?>

</section>
</div>

</body>
</html>
