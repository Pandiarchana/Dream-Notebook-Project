<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

$id = $_GET['id'] ?? '';
if (!is_numeric($id)) {
    die("Invalid entry ID");
}

$id = (int)$id;

// Get this diary entry
$stmt = $conn->prepare("SELECT * FROM diary_entries WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Entry not found");
}
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Entry – Dream Notebook</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
:root{
  --blue:#5a67d8;--blue-dk:#434aa8;--blue-lt:#e0e4ff;
  --bg:#f0f2f8;--white:#fff;--text:#1a1a2e;--muted:#6b7280;--border:#e5e7eb;
}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}

/* Sidebar */
.sidebar{width:220px;background:var(--white);border-right:1px solid var(--border);padding:20px;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;}
.user{display:flex;align-items:center;gap:10px;font-weight:600;margin-bottom:30px;}
.avatar{width:40px;height:40px;background:var(--blue);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;}
.menu a{display:block;padding:10px;border-radius:6px;text-decoration:none;color:var(--text);margin-bottom:6px;font-size:14px;transition:background .2s;}
.menu a:hover{background:var(--blue-lt);}
.menu a.active{background:var(--blue-lt);color:var(--blue);font-weight:600;}
.sidebar footer{margin-top:auto;font-size:13px;}
.sidebar footer a{display:block;margin-top:10px;color:var(--muted);text-decoration:none;}

/* Main */
.main{flex:1;padding:40px;overflow-y:auto;max-width:760px;}
.page-header{margin-bottom:32px;}
.page-header h1{font-family:'Fraunces',serif;font-size:32px;font-weight:900;margin-bottom:4px;}
.page-header p{color:var(--muted);font-size:14px;}

/* Form Card */
.form-card{background:var(--white);border-radius:16px;padding:32px;border:1px solid var(--border);}
.input-group{margin-bottom:22px;}
.input-group label{display:block;font-size:13px;font-weight:600;margin-bottom:7px;color:var(--text);}
.input-group input,
.input-group textarea{
  width:100%;padding:11px 14px;border-radius:8px;border:1.5px solid var(--border);
  font-size:14px;font-family:'DM Sans',sans-serif;color:var(--text);background:var(--bg);transition:border .2s;resize:vertical;
}
.input-group input:focus,
.input-group textarea:focus{outline:none;border-color:var(--blue);background:var(--white);}
.input-group textarea{min-height:180px;line-height:1.7;}
.save-btn{background:var(--blue);color:#fff;border:none;padding:13px 28px;border-radius:10px;cursor:pointer;font-size:15px;font-weight:600;width:100%;transition:background .2s;}
.save-btn:hover{background:var(--blue-dk);}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="user">
    <div class="avatar"><?= strtoupper(substr($username,0,1)) ?></div>
    <span><?= htmlspecialchars($username) ?></span>
  </div>
  <div class="menu">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="write_entry.php">✍️ Write Entry</a>
    <a href="history.php" class="active">📖 History</a>
    <a href="charts.php">📊 Charts</a>
    <a href="export.php">💾 Export & Backup</a>
    <a href="help.php">❓ Help</a>
  </div>
  <footer>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </footer>
</aside>

<main class="main">
  <div class="page-header">
    <h1>Edit Entry ✏️</h1>
    <p>Modify your dream diary entry</p>
  </div>

  <div class="form-card">
    <form action="save_edit_entry.php" method="POST">
      <input type="hidden" name="id" value="<?= $row['id'] ?>">
      <div class="input-group">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="<?= htmlspecialchars($row['title']) ?>" required>
      </div>
      <div class="input-group">
        <label for="content">Content</label>
        <textarea name="content" id="content" required><?= htmlspecialchars($row['content']) ?></textarea>
      </div>
      <button type="submit" class="save-btn">Save Changes</button>
    </form>
  </div>
</main>

</body>
</html>
