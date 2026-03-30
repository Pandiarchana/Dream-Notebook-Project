<?php
// dashboard.php — Dream Notebook Main Dashboard
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Get recent 3 entries
$stmt = $conn->prepare("SELECT * FROM diary_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent = $stmt->get_result();

// Get total count
$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM diary_entries WHERE user_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$total = $stmt2->get_result()->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – Dream Notebook</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    :root{
      --blue:#5a67d8;--blue-dk:#434aa8;--blue-lt:#e0e4ff;
      --bg:#f0f2f8;--white:#fff;--text:#1a1a2e;--muted:#6b7280;--border:#e5e7eb;
    }
    body{background:var(--bg);color:var(--text);font-family:'DM Sans',sans-serif;display:flex;min-height:100vh;}

    /* Sidebar */
    .sidebar{width:220px;background:var(--white);border-right:1px solid var(--border);padding:20px;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;flex-shrink:0;}
    .user{display:flex;align-items:center;gap:10px;font-weight:600;margin-bottom:30px;}
    .avatar{width:40px;height:40px;background:var(--blue);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;}
    .add-task{background:var(--blue);color:#fff;border:none;padding:10px;border-radius:6px;cursor:pointer;margin-bottom:20px;font-size:14px;font-family:'DM Sans',sans-serif;transition:background .2s;text-decoration:none;display:block;text-align:center;}
    .add-task:hover{background:var(--blue-dk);}
    .menu a{display:block;padding:10px;border-radius:6px;text-decoration:none;color:var(--text);margin-bottom:6px;font-size:14px;transition:background .2s;}
    .menu a:hover{background:var(--blue-lt);}
    .menu a.active{background:var(--blue-lt);color:var(--blue);font-weight:600;}
    .sidebar footer{margin-top:auto;font-size:13px;}
    .sidebar footer a{display:block;margin-top:10px;color:var(--muted);text-decoration:none;}
    .sidebar footer a:hover{text-decoration:underline;}

    /* Main */
    .main{flex:1;padding:40px;overflow-y:auto;}
    .page-header{margin-bottom:32px;animation:fadeUp .5s ease both;}
    .page-header h1{font-family:'Fraunces',serif;font-size:32px;font-weight:900;margin-bottom:4px;}
    .page-header p{color:var(--muted);font-size:14px;}

    /* Stats */
    .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:32px;}
    .stat{background:var(--white);border-radius:14px;padding:22px 24px;border:1px solid var(--border);animation:fadeUp .5s .1s ease both;}
    .stat-num{font-family:'Fraunces',serif;font-size:36px;font-weight:900;color:var(--blue);}
    .stat-label{font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px;}

    /* Quick actions */
    .quick{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:32px;animation:fadeUp .5s .2s ease both;}
    .quick-card{background:var(--white);border-radius:14px;padding:24px;border:1px solid var(--border);text-decoration:none;color:var(--text);transition:transform .2s,box-shadow .2s;display:flex;align-items:center;gap:16px;}
    .quick-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(90,103,216,.12);}
    .quick-icon{width:48px;height:48px;border-radius:12px;background:var(--blue-lt);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;}
    .quick-card h3{font-size:15px;font-weight:600;margin-bottom:2px;}
    .quick-card p{font-size:12px;color:var(--muted);}

    /* Recent entries */
    .section-title{font-family:'Fraunces',serif;font-size:20px;font-weight:700;margin-bottom:16px;}
    .entry-card{background:var(--white);border-radius:12px;padding:20px;border:1px solid var(--border);margin-bottom:12px;animation:fadeUp .5s .3s ease both;transition:transform .2s;}
    .entry-card:hover{transform:translateX(4px);}
    .entry-date{font-size:11px;color:var(--muted);margin-bottom:6px;}
    .entry-title{font-family:'Fraunces',serif;font-size:17px;font-weight:700;margin-bottom:6px;}
    .entry-preview{font-size:13px;color:var(--muted);line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
    .entry-actions{display:flex;gap:8px;margin-top:12px;}
    .btn-sm{padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:background .2s;}
    .btn-edit{background:var(--blue-lt);color:var(--blue);}
    .btn-edit:hover{background:#c7ceff;}
    .btn-delete{background:#fff0f0;color:#f76a6a;border:1px solid #ffd6d6;}
    .btn-delete:hover{background:#ffe0e0;}

    .no-entries{text-align:center;padding:40px;color:var(--muted);}
    .no-entries i{font-size:40px;display:block;margin-bottom:12px;opacity:.3;}

    .view-all{display:inline-block;margin-top:8px;color:var(--blue);font-size:13px;font-weight:600;text-decoration:none;}
    .view-all:hover{text-decoration:underline;}

    @keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
  </style>
</head>
<body>

<aside class="sidebar">
  <div class="user">
    <div class="avatar"><?= strtoupper(substr($username,0,1)) ?></div>
    <span><?= htmlspecialchars($username) ?></span>
  </div>
  <a href="write_entry.html" class="add-task">+ New Entry</a>
  <nav class="menu">
    <a href="dashboard.php" class="active">🏠 Dashboard</a>
    <a href="write_entry.php">✍️ Write Entry</a>
    <a href="history.php">📖 History</a>
    <a href="charts.php">📊 Charts</a>
    <a href="help.php">❓ Help</a>
  </nav>
  <footer>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </footer>
</aside>

<main class="main">

  <div class="page-header">
    <h1>Welcome back, <?= htmlspecialchars($username) ?>! 👋</h1>
    <p>Here's a summary of your dream journal</p>
  </div>

  <!-- Stats -->
  <div class="stats">
    <div class="stat">
      <div class="stat-num"><?= $total ?></div>
      <div class="stat-label">Total Entries</div>
    </div>
    <div class="stat">
      <div class="stat-num" style="color:#6af7a0">📓</div>
      <div class="stat-label">Dream Notebook</div>
    </div>
    <div class="stat">
      <div class="stat-num" style="font-size:16px;padding-top:8px"><?= date("M j, Y") ?></div>
      <div class="stat-label">Today</div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="quick">
    <a href="write_entry.html" class="quick-card">
      <div class="quick-icon">✍️</div>
      <div>
        <h3>Write New Entry</h3>
        <p>Record your dream or diary entry</p>
      </div>
    </a>
    <a href="charts.php" class="quick-card">
      <div class="quick-icon">📊</div>
      <div>
        <h3>View Charts</h3>
        <p>See your emotion trends and patterns</p>
      </div>
    </a>
  </div>

  <!-- Recent Entries -->
  <div class="section-title">Recent Entries</div>

  <?php if ($recent->num_rows === 0): ?>
  <div class="no-entries">
    <i class="fa-solid fa-moon"></i>
    <p>No entries yet. Start writing your first dream!</p>
    <a href="write_entry.html" style="color:var(--blue);font-weight:600;">Write now →</a>
  </div>
  <?php else: ?>
  <?php while ($row = $recent->fetch_assoc()): ?>
  <div class="entry-card">
    <div class="entry-date">📅 <?= date("F j, Y  g:i A", strtotime($row['created_at'])) ?></div>
    <div class="entry-title"><?= htmlspecialchars($row['title']) ?></div>
    <div class="entry-preview"><?= htmlspecialchars($row['content']) ?></div>
    <div class="entry-actions">
      <a href="edit_entry.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">✏️ Edit</a>
      <form action="delete_entry.php" method="POST" style="display:inline">
        <button type="submit" name="del" value="<?= $row['id'] ?>" class="btn-sm btn-delete"
          onclick="return confirm('Delete this entry?')">🗑 Delete</button>
      </form>
    </div>
  </div>
  <?php endwhile; ?>
  <a href="history.php" class="view-all">View all entries →</a>
  <?php endif; ?>

</main>
</body>
</html>