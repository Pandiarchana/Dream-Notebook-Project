<?php
// charts.php — Dream Notebook: Charts & Analytics Page
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Total entries
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM diary_entries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];

// Entries per day last 7 days
$stmt = $conn->prepare("
    SELECT DATE(created_at) as day, COUNT(*) as count
    FROM diary_entries
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at) ORDER BY day ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$days = []; $day_counts = [];
while ($row = $result->fetch_assoc()) {
    $days[]       = date("D d", strtotime($row['day']));
    $day_counts[] = (int)$row['count'];
}

// Entries per month last 6 months
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(created_at,'%b %Y') as month, COUNT(*) as count
    FROM diary_entries
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m') ORDER BY MIN(created_at) ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$months = []; $month_counts = [];
while ($row = $result->fetch_assoc()) {
    $months[]       = $row['month'];
    $month_counts[] = (int)$row['count'];
}

// Emotion analysis
$stmt = $conn->prepare("SELECT content FROM diary_entries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$all_text = '';
while ($row = $result->fetch_assoc()) {
    $all_text .= ' ' . strtolower($row['content']);
}

$emotion_map = [
    'Joy'      => ['happy','joy','excited','great','love','wonderful','amazing','smile','laugh','fun','glad','delight'],
    'Fear'     => ['scared','fear','afraid','nightmare','dark','monster','run','chase','terrified','anxious','panic'],
    'Stress'   => ['stress','worried','anxious','pressure','late','rush','fail','exam','deadline','nervous','overwhelm'],
    'Sadness'  => ['sad','cry','loss','miss','alone','lonely','hurt','pain','grief','tears','lost','broken'],
    'Peace'    => ['calm','peaceful','quiet','relax','rest','serene','still','gentle','safe','comfort','dream','float'],
    'Surprise' => ['surprise','sudden','unexpected','shock','wow','strange','weird','unusual','suddenly','appeared'],
];
$emotion_colors = ['#f6d860','#7c6af7','#f76a6a','#6ac0f7','#6af7a0','#f7a06a'];

$emotion_scores = [];
foreach ($emotion_map as $emotion => $keywords) {
    $score = 0;
    foreach ($keywords as $kw) { $score += substr_count($all_text, $kw); }
    $emotion_scores[$emotion] = $score;
}

// Top keywords
$stop_words = ['the','a','an','and','or','but','in','on','at','to','for','of','with','is','was','i','my','me','it','this','that','he','she','they','we','had','have','be','so','up','do','if','as','by','no','not','are','from','his','her','its','our','your','their','what','which','who','when','where','how','all','been','were','will','would','could','should','very','just','then','than','about','into','like','more','also','after','before'];
$words    = str_word_count(strtolower($all_text), 1);
$filtered = array_filter($words, fn($w) => !in_array($w, $stop_words) && strlen($w) > 3);
$freq     = array_count_values($filtered);
arsort($freq);
$top_keywords = array_slice($freq, 0, 8, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Charts – Dream Notebook</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
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
    .avatar{width:40px;height:40px;background:var(--blue);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;}
    .add-task{background:var(--blue);color:#fff;border:none;padding:10px;border-radius:6px;cursor:pointer;margin-bottom:20px;font-size:14px;font-family:'DM Sans',sans-serif;text-decoration:none;display:block;text-align:center;}
    .add-task:hover{background:var(--blue-dk);}
    .menu a{display:block;padding:10px;border-radius:6px;text-decoration:none;color:var(--text);margin-bottom:6px;font-size:14px;transition:background .2s;}
    .menu a:hover{background:var(--blue-lt);}
    .menu a.active{background:var(--blue-lt);color:var(--blue);font-weight:600;}
    .sidebar footer{margin-top:auto;font-size:13px;}
    .sidebar footer a{display:block;margin-top:10px;color:var(--muted);text-decoration:none;}

    /* Main */
    .main{flex:1;padding:40px;overflow-y:auto;}
    .page-header{margin-bottom:32px;}
    .page-header h1{font-family:'Fraunces',serif;font-size:32px;font-weight:900;margin-bottom:4px;}
    .page-header p{color:var(--muted);font-size:14px;}

    /* Stats row */
    .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px;}
    .stat{background:var(--white);border-radius:14px;padding:20px 24px;border:1px solid var(--border);}
    .stat-num{font-family:'Fraunces',serif;font-size:34px;font-weight:900;color:var(--blue);}
    .stat-label{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-top:4px;}

    /* Chart cards */
    .charts-grid{display:grid;grid-template-columns:1.5fr 1fr;gap:20px;margin-bottom:24px;}
    .chart-card{background:var(--white);border-radius:14px;padding:24px;border:1px solid var(--border);}
    .chart-card h2{font-family:'Fraunces',serif;font-size:17px;font-weight:700;margin-bottom:4px;}
    .chart-sub{font-size:12px;color:var(--muted);margin-bottom:18px;}
    .chart-wrap{position:relative;height:210px;}

    /* Emotion bars */
    .emotion-card{background:var(--white);border-radius:14px;padding:24px;border:1px solid var(--border);margin-bottom:24px;}
    .emotion-card h2{font-family:'Fraunces',serif;font-size:17px;font-weight:700;margin-bottom:4px;}
    .emo-row{display:flex;align-items:center;gap:12px;margin-top:12px;}
    .emo-name{width:72px;font-size:12px;font-weight:600;flex-shrink:0;}
    .emo-bar-bg{flex:1;height:10px;background:#f0f2f8;border-radius:99px;overflow:hidden;}
    .emo-bar-fill{height:100%;border-radius:99px;transition:width 1s cubic-bezier(.4,0,.2,1);}
    .emo-count{font-size:12px;color:var(--muted);width:24px;text-align:right;flex-shrink:0;}

    /* Keywords */
    .kw-card{background:var(--white);border-radius:14px;padding:24px;border:1px solid var(--border);margin-bottom:24px;}
    .kw-card h2{font-family:'Fraunces',serif;font-size:17px;font-weight:700;margin-bottom:4px;}
    .kw-cloud{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px;}
    .kw-pill{padding:6px 16px;border-radius:99px;font-size:13px;font-weight:500;border:1.5px solid;cursor:default;transition:transform .15s;}
    .kw-pill:hover{transform:translateY(-2px);}

    .no-data{text-align:center;padding:40px;color:var(--muted);font-size:13px;}

    @media(max-width:768px){
      .sidebar{display:none;}
      .charts-grid{grid-template-columns:1fr;}
      .stats{grid-template-columns:1fr 1fr;}
      .main{padding:20px;}
    }
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
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="write_entry.php">✍️ Write Entry</a>
    <a href="history.php">📖 History</a>
    <a href="charts.php" class="active">📊 Charts</a>
    <a href="help.php">❓ Help</a>
  </nav>
  <footer>
    <a href="../backend/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </footer>
</aside>

<main class="main">
  <div class="page-header">
    <h1>Charts & Analytics 📊</h1>
    <p>Visual insights from your dream and diary entries</p>
  </div>

  <?php if ($total == 0): ?>
  <div class="no-data">
    <i class="fa-solid fa-chart-bar" style="font-size:48px;display:block;margin-bottom:12px;opacity:.3"></i>
    <p>No entries yet. Write some diary entries to see your charts!</p>
    <a href="write_entry.html" style="color:var(--blue);font-weight:600;">Write now →</a>
  </div>
  <?php else: ?>

  <!-- Stats -->
  <div class="stats">
    <div class="stat">
      <div class="stat-num"><?= $total ?></div>
      <div class="stat-label">Total Entries</div>
    </div>
    <div class="stat">
      <div class="stat-num"><?= count($days) ?></div>
      <div class="stat-label">Active Days (7 days)</div>
    </div>
    <div class="stat">
      <div class="stat-num"><?= array_sum(array_values($emotion_scores)) ?></div>
      <div class="stat-label">Emotions Detected</div>
    </div>
  </div>

  <!-- Line + Doughnut -->
  <div class="charts-grid">
    <div class="chart-card">
      <h2>Entries This Week</h2>
      <p class="chart-sub">Number of entries written per day</p>
      <div class="chart-wrap"><canvas id="lineChart"></canvas></div>
    </div>
    <div class="chart-card">
      <h2>Emotion Breakdown</h2>
      <p class="chart-sub">Distribution across all entries</p>
      <div class="chart-wrap"><canvas id="doughnutChart"></canvas></div>
    </div>
  </div>

  <!-- Monthly bar chart -->
  <div class="chart-card" style="margin-bottom:24px;">
    <h2>Monthly Activity</h2>
    <p class="chart-sub">Entries written per month over the last 6 months</p>
    <div class="chart-wrap"><canvas id="barChart"></canvas></div>
  </div>

  <!-- Emotion bars -->
  <div class="emotion-card">
    <h2>Emotion Tracker</h2>
    <p class="chart-sub">Emotions detected from keywords in your entries</p>
    <?php
    $max = max(array_values($emotion_scores)) ?: 1;
    $ei  = 0;
    foreach ($emotion_scores as $emo => $score):
      $col = $emotion_colors[$ei % count($emotion_colors)];
      $ei++;
    ?>
    <div class="emo-row">
      <span class="emo-name"><?= $emo ?></span>
      <div class="emo-bar-bg">
        <div class="emo-bar-fill" style="width:<?= ($score/$max)*100 ?>%;background:<?= $col ?>"></div>
      </div>
      <span class="emo-count"><?= $score ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Keywords -->
  <div class="kw-card">
    <h2>Top Keywords</h2>
    <p class="chart-sub">Most frequent words in your entries</p>
    <?php if (empty($top_keywords)): ?>
      <p style="color:var(--muted);font-size:13px;margin-top:12px">Write more entries to see keywords!</p>
    <?php else: ?>
    <div class="kw-cloud">
      <?php
      $kw_colors = [
        ["#e0e4ff","#5a67d8"],["#fef3c7","#d97706"],["#dcfce7","#16a34a"],
        ["#fce7f3","#db2777"],["#ede9fe","#7c3aed"],["#fff7ed","#ea580c"],
        ["#f0fdf4","#15803d"],["#fdf4ff","#a21caf"],
      ];
      $ki = 0;
      foreach ($top_keywords as $word => $freq):
        $col  = $kw_colors[$ki % count($kw_colors)];
        $size = max(12, min(20, 12 + $freq * 1.5));
        $ki++;
      ?>
      <span class="kw-pill" style="background:<?= $col[0] ?>;color:<?= $col[1] ?>;border-color:<?= $col[1] ?>33;font-size:<?= $size ?>px">
        <?= htmlspecialchars($word) ?> <sup style="font-size:9px;opacity:.7"><?= $freq ?></sup>
      </span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php endif; ?>
</main>

<script>
Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.color = "#6b7280";

const days       = <?= json_encode(array_values($days)) ?>;
const dayCounts  = <?= json_encode(array_values($day_counts)) ?>;
const months     = <?= json_encode(array_values($months)) ?>;
const monthCounts= <?= json_encode(array_values($month_counts)) ?>;
const emotions   = <?= json_encode($emotion_scores) ?>;
const emoColors  = <?= json_encode($emotion_colors) ?>;

// Line chart
new Chart(document.getElementById('lineChart'), {
  type: 'line',
  data: {
    labels: days.length ? days : ['No data'],
    datasets: [{
      label: 'Entries', data: dayCounts.length ? dayCounts : [0],
      borderColor:'#5a67d8', backgroundColor:'rgba(90,103,216,.08)',
      borderWidth:2.5, pointBackgroundColor:'#5a67d8',
      pointRadius:5, tension:0.4, fill:true
    }]
  },
  options:{ responsive:true, maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{ y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#f0f2f8'}}, x:{grid:{display:false}} }
  }
});

// Doughnut chart
const emoVals = Object.values(emotions);
const hasData = emoVals.some(v => v > 0);
new Chart(document.getElementById('doughnutChart'), {
  type: 'doughnut',
  data: {
    labels: Object.keys(emotions),
    datasets: [{
      data: hasData ? emoVals : [1,1,1,1,1,1],
      backgroundColor: hasData ? emoColors : Array(6).fill('#e5e7eb'),
      borderWidth:0, hoverOffset:8
    }]
  },
  options:{ responsive:true, maintainAspectRatio:false, cutout:'65%',
    plugins:{ legend:{ position:'bottom', labels:{boxWidth:10,padding:10,font:{size:11}} }, tooltip:{enabled:hasData} }
  }
});

// Bar chart
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: months.length ? months : ['No data'],
    datasets: [{
      label: 'Entries', data: monthCounts.length ? monthCounts : [0],
      backgroundColor:'rgba(90,103,216,.7)', borderRadius:6, borderSkipped:false
    }]
  },
  options:{ responsive:true, maintainAspectRatio:false,
    plugins:{legend:{display:false}},
    scales:{ y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#f0f2f8'}}, x:{grid:{display:false}} }
  }
});
</script>
</body>
</html>