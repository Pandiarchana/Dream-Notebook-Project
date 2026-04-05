<?php
// history.php — View all diary entries with emotion charts
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Get all diaries
$stmt = $conn->prepare("SELECT * FROM diary_entries WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$entries = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>History – Dream Notebook</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --blue:#5a67d8;--blue-dk:#434aa8;--blue-lt:#e0e4ff;
  --bg:#f0f2f8;--white:#fff;--text:#1a1a2e;--muted:#6b7280;--border:#e5e7eb;
}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;margin:0;}

/* Sidebar */
.sidebar{
  width:220px;background:var(--white);border-right:1px solid var(--border);
  padding:20px;display:flex;flex-direction:column;position:sticky;top:0;height:100vh;
}
.user{display:flex;align-items:center;gap:10px;font-weight:600;margin-bottom:30px;}
.avatar{width:40px;height:40px;background:var(--blue);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;}
.sidebar a{display:block;padding:10px;border-radius:6px;text-decoration:none;color:var(--text);margin-bottom:6px;font-size:14px;transition:background .2s;}
.sidebar a:hover{background:var(--blue-lt);}
.sidebar a.active{background:var(--blue-lt);color:var(--blue);font-weight:600;}
.sidebar footer{margin-top:auto;font-size:13px;}
.sidebar footer a{display:block;margin-top:10px;color:var(--muted);text-decoration:none;}

/* Main */
.main{flex:1;padding:40px;overflow-y:auto;max-width:800px;}
.page-header{margin-bottom:32px;}
.page-header h1{font-family:'Fraunces',serif;font-size:32px;font-weight:900;margin-bottom:4px;}
.page-header p{color:var(--muted);font-size:14px;}

/* Entry Card */
.entry-card{
  background:var(--white);border-radius:12px;padding:20px;border:1px solid var(--border);
  margin-bottom:12px;transition:transform .2s;
}
.entry-card:hover{transform:translateX(4px);}
.entry-date{font-size:11px;color:var(--muted);margin-bottom:6px;}
.entry-title{font-family:'Fraunces',serif;font-size:17px;font-weight:700;margin-bottom:6px;}
.entry-preview{font-size:13px;color:var(--muted);line-height:1.6;}
.entry-actions{display:flex;gap:8px;margin-top:12px;}
.btn-sm{padding:6px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:background .2s;}
.btn-edit{background:var(--blue-lt);color:var(--blue);}
.btn-edit:hover{background:#c7ceff;}
.btn-delete{background:#fff0f0;color:#f76a6a;border:1px solid #ffd6d6;}
.btn-delete:hover{background:#ffe0e0;}
.btn-chart{background:var(--blue);color:#fff;}
.btn-chart:hover{background:var(--blue-dk);}

/* Chart Modal */
#chartModal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background: rgba(0,0,0,0.5);}
#chartContainer{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:var(--white);padding:20px;border-radius:6px;width:80%;max-width:700px;}
#closeChart{margin-top:10px;padding:5px 10px;background:red;color:white;border:none;cursor:pointer;}
</style>
</head>
<body>

<aside class="sidebar">
  <div class="user">
    <div class="avatar"><?= strtoupper(substr($username,0,1)) ?></div>
    <span><?= htmlspecialchars($username) ?></span>
  </div>
  <a href="dashboard.php">🏠 Dashboard</a>
  <a href="write_entry.php">✍️ Write Entry</a>
  <a href="#" class="active">📖 History</a>
  <a href="charts.php">📊 Charts</a>
  <a href="export.php">💾 Export & Backup</a>
  <a href="help.php">❓ Help</a>
  <footer>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </footer>
</aside>

<main class="main">
  <div class="page-header">
    <h1>History 📖</h1>
    <p>View and analyze your past diary entries</p>
  </div>

  <?php if ($entries->num_rows === 0): ?>
    <div class="no-entries" style="text-align:center;padding:40px;color:var(--muted);">
      <i class="fa-solid fa-moon" style="font-size:40px;opacity:.3;"></i>
      <p>No entries yet. Start writing your first dream!</p>
      <a href="write_entry.php" style="color:var(--blue);font-weight:600;">Write now →</a>
    </div>
  <?php else: ?>
    <?php while ($row = $entries->fetch_assoc()): ?>

    <?php
    // 调用 Python 脚本
    $process = proc_open(
        "python analyze_emotion_advanced.py",
        [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ],
        $pipes
    );

    $emotion_data = null;
    if (is_resource($process)) {
        fwrite($pipes[0], $row['content']);
        fclose($pipes[0]);
        $json = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        stream_get_contents($pipes[2]);
        proc_close($process);
        if ($json) $emotion_data = json_decode($json,true);
    }
    ?>

    <div class="entry-card">
      <div class="entry-date">📅 <?= date("F j, Y  g:i A", strtotime($row['created_at'])) ?></div>
      <div class="entry-title"><?= htmlspecialchars($row['title']) ?></div>
      <div class="entry-preview"><?= htmlspecialchars($row['content']) ?></div>

      <?php if($emotion_data): ?>
      <div style="margin-top:10px;font-size:13px;color:var(--text);">
        <strong>Overall Emotion:</strong> <?= $emotion_data['overall_emotion'] ?? 'Unknown' ?>
        &nbsp;&nbsp;
        <strong>Risk Level:</strong> <?= $emotion_data['risk_level'] ?? 'Low' ?>
      </div>
      <?php endif; ?>

      <div class="entry-actions">
        <a href="edit_entry.php?id=<?= $row['id'] ?>" class="btn-sm btn-edit">✏️ Edit</a>
        <form action="delete_entry.php" method="POST" style="display:inline">
          <button type="submit" name="del" value="<?= $row['id'] ?>" class="btn-sm btn-delete"
            onclick="return confirm('Delete this entry?')">🗑 Delete</button>
        </form>
        <?php if($emotion_data): ?>
        <button class="btn-sm btn-chart" data-trend='<?= htmlspecialchars(json_encode($emotion_data['sentence_analysis'])) ?>'>📈 Show Emotion Chart</button>
        <?php endif; ?>
      </div>
    </div>

    <?php endwhile; ?>
  <?php endif; ?>
</main>

<!-- Chart Modal -->
<div id="chartModal">
  <div id="chartContainer">
    <canvas id="emotionChart"></canvas>
    <button id="closeChart">Close</button>
  </div>
</div>

<script>
const modal = document.getElementById('chartModal');
const ctx = document.getElementById('emotionChart').getContext('2d');
let chartInstance;

document.querySelectorAll(".btn-chart").forEach(btn=>{
    btn.addEventListener("click", function(){
        const data = JSON.parse(this.dataset.trend);
        const trend = data.map(s=>s.polarity);
        const labels = data.map((s,i)=>`Sentence ${i+1}`);

        if(chartInstance) chartInstance.destroy();
        chartInstance = new Chart(ctx,{
            type:'line',
            data:{
                labels:labels,
                datasets:[{
                    label:'Emotion Polarity',
                    data:trend,
                    borderColor:'blue',
                    tension:0.3,
                    fill:false
                }]
            },
            options:{scales:{y:{min:-1,max:1}}}
        });

        modal.style.display='block';
    });
});

document.getElementById('closeChart').addEventListener('click', ()=>{
    modal.style.display='none';
});
</script>
</body>
</html>
