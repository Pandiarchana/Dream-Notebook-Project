<?php
// UserPage.php — Dream Notebook Main Diary Page
// FIXES APPLIED (Member 4 – Integration):
// 1. Renamed from UserPage.html → UserPage.php (required for PHP to execute)
// 2. Added session_start() and authentication check
// 3. Added db_connect.php include
// 4. Wrapped entry form inputs in <form> with correct name attributes
// 5. Fixed PHP query: correct table (diary_entries) and column names (content, id)
// 6. Fixed delete form action: Delete.php → delete_entry.php
// 7. Fixed logout link to go through logout.php
// 8. Replaced string interpolation SQL with prepared statement (security fix)

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: Userlogin.html");
    exit();
}
include '../backend code/db_connect.php';

$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dream Diary</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * { box-sizing:border-box; font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif; margin:0; padding:0; }
    body { background:#f0f2f8; color:#333; }
    .app { display:flex; height:100vh; }

    /* Sidebar */
    .sidebar { width:220px; background:#fff; border-right:1px solid #ddd; padding:20px; display:flex; flex-direction:column; }
    .user { display:flex; align-items:center; gap:10px; font-weight:600; margin-bottom:30px; }
    .avatar { width:40px; height:40px; background:#5a67d8; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; }
    .add-task { background:#5a67d8; color:white; border:none; padding:10px; border-radius:6px; cursor:pointer; margin-bottom:20px; transition:background .3s; font-size:14px; }
    .add-task:hover { background:#434aa8; }
    .menu a { display:block; padding:10px; border-radius:6px; text-decoration:none; color:#333; margin-bottom:8px; transition:background .3s; }
    .menu a.active, .menu a:hover { background:#e0e4ff; }
    footer { margin-top:auto; font-size:13px; }
    footer a { display:block; margin-top:10px; color:#555; text-decoration:none; }
    footer a:hover { text-decoration:underline; }

    /* Main */
    .main-section { flex:1; padding:40px; overflow-y:auto; }
    .entry-form { background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,.05); margin-bottom:40px; }
    .entry-form h1 { margin-bottom:10px; color:#5a67d8; }
    .entry-form p { margin-bottom:20px; color:#666; }
    .input-group { display:flex; flex-direction:column; margin-bottom:20px; }
    .input-group label { margin-bottom:6px; font-weight:500; }
    .input-group input, .input-group textarea { padding:10px; border-radius:6px; border:1px solid #ccc; font-size:14px; resize:vertical; }
    .input-group textarea { min-height:100px; }
    .save-btn { background:#5a67d8; color:white; border:none; padding:12px 20px; border-radius:8px; cursor:pointer; transition:background .3s; font-size:14px; }
    .save-btn:hover { background:#434aa8; }

    /* Entries */
    .entries h2 { margin-bottom:20px; color:#5a67d8; }
    .entry-container { display:flex; flex-direction:column; align-items:center; gap:30px; }
    .entry-card { background:#fff; border-radius:20px; box-shadow:0 6px 20px rgba(0,0,0,.1); width:100%; overflow:hidden; transition:transform .3s; display:flex; flex-direction:column; }
    .entry-card:hover { transform:translateY(-4px); }
    .entry-header { background:#f7f7f7; padding:15px; text-align:center; border-bottom:1px solid #ddd; }
    .entry-header h2 { color:#5a67d8; font-size:22px; font-weight:bold; margin:0; }
    .entry-body { display:flex; padding:15px; gap:20px; }
    .entry-details { flex:1; display:flex; flex-direction:column; justify-content:space-between; }
    .entry-details p { font-size:15px; color:#444; line-height:1.5; margin-bottom:10px; }
    .entry-details h3 { color:#7588a7; margin-bottom:8px; font-size:16px; }
    .btn-group { display:flex; justify-content:flex-end; gap:10px; margin-top:12px; }
    .btn-group button { background:#5a67d8; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-size:14px; font-weight:bold; transition:background .3s; }
    .btn-group button:hover { background:#434aa8; }
    .btn-group .btn-delete { background:#f76a6a; }
    .btn-group .btn-delete:hover { background:#e55555; }

    .no-entries { text-align:center; padding:40px; color:#9ca3af; }
    .no-entries i { font-size:48px; margin-bottom:16px; display:block; }
  </style>
</head>
<body>
<div class="app">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="user">
      <div class="avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
      <span><?= htmlspecialchars($username) ?></span>
    </div>
    <button class="add-task" onclick="document.getElementById('entryForm').scrollIntoView({behavior:'smooth'})">+ Add Entry</button>
    <nav class="menu">
      <a href="UserPage.php" class="active">🏠 Welcome</a>
      <a href="UserPage.php">✍️ Create</a>
      <a href="view_entries.php">📖 View Entries</a>
      <a href="summary.php">📊 Summary</a>
    </nav>
    <footer>
      <a href="help.html">❓ Help &amp; resources</a>
      <a href="../backend code/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </footer>
  </aside>

  <!-- Main -->
  <section class="main-section">

    <!-- Entry Form — FIXED: wrapped in <form> with correct name attributes -->
    <div class="entry-form" id="entryForm">
      <h1>Dream Diary</h1>
      <p>Record your dreams below:</p>

      <form action="../backend code/create_entry.php" method="POST">
        <div class="input-group">
          <label for="title">Title:</label>
          <input type="text" name="title" id="title" placeholder="Give your entry a title" required>
        </div>
        <div class="input-group">
          <label for="content">Your Dream:</label>
          <textarea name="content" id="content" placeholder="Describe your dream..." required></textarea>
        </div>
        <button type="submit" class="save-btn">Save Entry</button>
      </form>
    </div>

    <!-- Entries List — FIXED: correct table, columns, prepared statement, delete path -->
    <div class="entries">
      <h2>Your Entries</h2>
      <div class="entry-container">
        <?php
        $stmt = $conn->prepare(
            "SELECT * FROM diary_entries WHERE user_id = ? ORDER BY created_at DESC"
        );
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0):
        ?>
        <div class="no-entries">
          <i class="fa-solid fa-moon"></i>
          <p>No entries yet. Write your first dream above!</p>
        </div>
        <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="entry-card">
          <div class="entry-header">
            <h2><?= htmlspecialchars($row['title']) ?></h2>
          </div>
          <div class="entry-body">
            <div class="entry-details">
              <h3>📅 <?= date("F j, Y  g:i A", strtotime($row['created_at'])) ?></h3>
              <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
              <div class="btn-group">
                <!-- Delete -->
                <form action="../backend code/delete_entry.php" method="POST" style="display:inline">
                  <button type="submit" name="del" value="<?= $row['id'] ?>"
                    class="btn-delete"
                    onclick="return confirm('Delete this entry?')">
                    🗑 Delete
                  </button>
                </form>
                <!-- Edit -->
                <a href="../backend code/edit_form.php?id=<?= $row['id'] ?>">
                  <button type="button">✏️ Edit</button>
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
        <?php endif; ?>
      </div>
    </div>

  </section>
</div>
</body>
</html>
