<?php
// summary.php — Dream Notebook: Summary & Analytics Page
// Member 4: Integration & Project Management
session_start();
include '../backend code/db_connect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: Userlogin.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// ── 1. Total entries ──────────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM diary_entries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_entries = $stmt->get_result()->fetch_assoc()['total'];

// ── 2. Entries per day (last 7 days) for line chart ──────────
$stmt = $conn->prepare("
    SELECT DATE(created_at) as day, COUNT(*) as count
    FROM diary_entries
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$days = []; $counts = [];
while ($row = $result->fetch_assoc()) {
    $days[]   = date("D d", strtotime($row['day']));
    $counts[] = (int)$row['count'];
}

// ── 3. Most recent entry date ────────────────────────────────
$stmt = $conn->prepare("SELECT MAX(created_at) as last FROM diary_entries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$last_entry = $stmt->get_result()->fetch_assoc()['last'];
$last_entry_fmt = $last_entry ? date("M j, Y", strtotime($last_entry)) : "No entries yet";

// ── 4. Simple keyword extraction from all entries ────────────
// (Until Gemini API is integrated, we do basic word frequency)
$stmt = $conn->prepare("SELECT title, content FROM diary_entries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$all_text = "";
while ($row = $result->fetch_assoc()) {
    $all_text .= " " . $row['title'] . " " . $row['content'];
}

// Strip common stop words
$stop_words = ["the","a","an","and","or","but","in","on","at","to","for","of","with",
               "is","was","i","my","me","it","this","that","he","she","they","we",
               "had","have","be","so","up","do","if","as","by","no","not","are","from",
               "his","her","its","our","your","their","what","which","who","when",
               "where","how","all","been","were","will","would","could","should","very",
               "just","then","than","about","into","like","more","also","after","before"];

$words = str_word_count(strtolower($all_text), 1);
$filtered = array_filter($words, fn($w) => !in_array($w, $stop_words) && strlen($w) > 3);
$freq = array_count_values($filtered);
arsort($freq);
$top_keywords = array_slice($freq, 0, 12, true);

// ── 5. Basic emotion detection from keywords ─────────────────
$emotion_map = [
    "joy"     => ["happy","joy","excited","great","love","wonderful","amazing","smile","laugh","fun","glad","delight"],
    "fear"    => ["scared","fear","afraid","nightmare","dark","monster","run","chase","terrified","anxious","panic"],
    "stress"  => ["stress","worried","anxious","pressure","late","rush","fail","exam","deadline","nervous","overwhelm"],
    "sadness" => ["sad","cry","loss","miss","alone","lonely","hurt","pain","grief","tears","lost","broken"],
    "peace"   => ["calm","peaceful","quiet","relax","rest","serene","still","gentle","safe","comfort","dream","float"],
    "surprise"=> ["surprise","sudden","unexpected","shock","wow","strange","weird","unusual","suddenly","appeared"],
];

$emotion_scores = ["joy"=>0,"fear"=>0,"stress"=>0,"sadness"=>0,"peace"=>0,"surprise"=>0];
foreach ($filtered as $word) {
    foreach ($emotion_map as $emotion => $keywords) {
        if (in_array($word, $keywords)) $emotion_scores[$emotion]++;
    }
}
$emotion_colors = [
    "joy"     => "#f6d860",
    "fear"    => "#7c6af7",
    "stress"  => "#f76a6a",
    "sadness" => "#6ac0f7",
    "peace"   => "#6af7a0",
    "surprise"=> "#f7a06a",
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Summary – Dream Notebook</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --blue:    #5a67d8;
      --blue-dk: #434aa8;
      --blue-lt: #e0e4ff;
      --bg:      #f0f2f8;
      --white:   #ffffff;
      --text:    #1a1a2e;
      --muted:   #6b7280;
      --border:  #e5e7eb;

      --joy:     #f6d860;
      --fear:    #7c6af7;
      --stress:  #f76a6a;
      --sadness: #6ac0f7;
      --peace:   #6af7a0;
      --surprise:#f7a06a;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      display: flex;
    }

    /* ── Sidebar (matches UserPage) ── */
    .sidebar {
      width: 220px;
      background: var(--white);
      border-right: 1px solid var(--border);
      padding: 20px;
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
      flex-shrink: 0;
    }
    .user { display:flex; align-items:center; gap:10px; font-weight:600; margin-bottom:30px; }
    .avatar {
      width:40px; height:40px; background:var(--blue); color:#fff;
      border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700;
    }
    .add-task {
      background:var(--blue); color:#fff; border:none; padding:10px;
      border-radius:6px; cursor:pointer; margin-bottom:20px; font-family:'DM Sans',sans-serif;
      font-size:14px; transition:background .2s;
    }
    .add-task:hover { background:var(--blue-dk); }
    .menu a {
      display:block; padding:10px; border-radius:6px; text-decoration:none;
      color:var(--text); margin-bottom:6px; font-size:14px; transition:background .2s;
    }
    .menu a:hover { background:var(--blue-lt); }
    .menu a.active { background:var(--blue-lt); color:var(--blue); font-weight:600; }
    .sidebar footer { margin-top:auto; font-size:13px; }
    .sidebar footer a { display:block; margin-top:10px; color:var(--muted); text-decoration:none; }
    .sidebar footer a:hover { text-decoration:underline; }

    /* ── Main ── */
    .main { flex:1; padding:36px 40px; overflow-y:auto; }

    .page-header { margin-bottom:32px; animation: fadeUp .5s ease both; }
    .page-header h1 {
      font-family: 'Fraunces', serif;
      font-size: 34px; font-weight:700; color:var(--text);
      margin-bottom:4px;
    }
    .page-header p { color:var(--muted); font-size:14px; }

    /* ── Stat cards ── */
    .stats-row {
      display:grid; grid-template-columns:repeat(3,1fr); gap:16px;
      margin-bottom:32px;
    }
    .stat-card {
      background:var(--white); border-radius:14px; padding:22px 24px;
      border:1px solid var(--border); position:relative; overflow:hidden;
      animation: fadeUp .5s ease both;
    }
    .stat-card:nth-child(2) { animation-delay:.08s; }
    .stat-card:nth-child(3) { animation-delay:.16s; }
    .stat-card::before {
      content:''; position:absolute; top:-30px; right:-30px;
      width:90px; height:90px; border-radius:50%;
      opacity:.12;
    }
    .stat-card:nth-child(1)::before { background:var(--blue); }
    .stat-card:nth-child(2)::before { background:#6af7a0; }
    .stat-card:nth-child(3)::before { background:#f7a06a; }

    .stat-icon { font-size:20px; margin-bottom:10px; }
    .stat-num  { font-family:'Fraunces',serif; font-size:38px; font-weight:700; color:var(--blue); line-height:1; }
    .stat-label{ font-size:12px; color:var(--muted); margin-top:4px; text-transform:uppercase; letter-spacing:.06em; }

    /* ── Charts grid ── */
    .charts-grid {
      display:grid; grid-template-columns:1.6fr 1fr; gap:20px; margin-bottom:28px;
    }
    .chart-card {
      background:var(--white); border-radius:14px; padding:24px;
      border:1px solid var(--border);
      animation: fadeUp .5s .2s ease both;
    }
    .chart-card h2 {
      font-family:'Fraunces',serif; font-size:18px; font-weight:700;
      margin-bottom:4px; color:var(--text);
    }
    .chart-card .chart-sub { font-size:12px; color:var(--muted); margin-bottom:20px; }
    .chart-wrap { position:relative; height:220px; }

    /* ── Emotion bar chart card ── */
    .emotion-card {
      background:var(--white); border-radius:14px; padding:24px;
      border:1px solid var(--border); margin-bottom:28px;
      animation: fadeUp .5s .28s ease both;
    }
    .emotion-card h2 { font-family:'Fraunces',serif; font-size:18px; font-weight:700; margin-bottom:4px; }
    .emotion-card .chart-sub { font-size:12px; color:var(--muted); margin-bottom:20px; }
    .emotion-bars { display:flex; flex-direction:column; gap:12px; }
    .emo-row { display:flex; align-items:center; gap:12px; }
    .emo-name {
      width:72px; font-size:12px; text-transform:capitalize;
      font-weight:600; color:var(--text); flex-shrink:0;
    }
    .emo-bar-bg {
      flex:1; height:10px; background:#f0f2f8; border-radius:99px; overflow:hidden;
    }
    .emo-bar-fill {
      height:100%; border-radius:99px;
      transition: width 1s cubic-bezier(.4,0,.2,1);
    }
    .emo-count { font-size:12px; color:var(--muted); width:24px; text-align:right; flex-shrink:0; }

    /* ── Keywords ── */
    .keywords-card {
      background:var(--white); border-radius:14px; padding:24px;
      border:1px solid var(--border); margin-bottom:28px;
      animation: fadeUp .5s .36s ease both;
    }
    .keywords-card h2 { font-family:'Fraunces',serif; font-size:18px; font-weight:700; margin-bottom:4px; }
    .keywords-card .chart-sub { font-size:12px; color:var(--muted); margin-bottom:18px; }
    .keyword-cloud { display:flex; flex-wrap:wrap; gap:10px; }
    .kw-pill {
      padding:6px 16px; border-radius:99px; font-size:13px; font-weight:500;
      border:1.5px solid; cursor:default;
      transition: transform .15s, box-shadow .15s;
    }
    .kw-pill:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(90,103,216,.15); }

    /* ── AI notice ── */
    .ai-notice {
      background: linear-gradient(135deg, #5a67d8 0%, #7c6af7 100%);
      border-radius:14px; padding:24px 28px; color:#fff;
      display:flex; align-items:center; gap:20px;
      animation: fadeUp .5s .44s ease both;
    }
    .ai-notice-icon { font-size:36px; flex-shrink:0; }
    .ai-notice h3 { font-family:'Fraunces',serif; font-size:18px; margin-bottom:4px; }
    .ai-notice p { font-size:13px; opacity:.85; line-height:1.6; }

    /* ── Empty state ── */
    .empty {
      text-align:center; padding:60px 20px; color:var(--muted);
    }
    .empty i { font-size:48px; margin-bottom:16px; opacity:.3; display:block; }
    .empty p { font-size:14px; }

    @keyframes fadeUp {
      from { opacity:0; transform:translateY(18px); }
      to   { opacity:1; transform:translateY(0); }
    }

    @media (max-width: 768px) {
      .sidebar { display:none; }
      .stats-row { grid-template-columns:1fr 1fr; }
      .charts-grid { grid-template-columns:1fr; }
      .main { padding:20px; }
    }
  </style>
</head>
<body>

<!-- ── Sidebar ── -->
<aside class="sidebar">
  <div class="user">
    <div class="avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
    <span><?= htmlspecialchars($username) ?></span>
  </div>
  <a href="create_entry.php" class="add-task">+ Add Entry</a>
  <nav class="menu">
    <a href="UserPage.php">🏠 Welcome</a>
    <a href="UserPage.php">✍️ Create</a>
    <a href="view_entries.php">📖 View Entries</a>
    <a href="summary.php" class="active">📊 Summary</a>
  </nav>
  <footer>
    <a href="#">❓ Help &amp; resources</a>
    <a href="../backend code/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </footer>
</aside>

<!-- ── Main Content ── -->
<main class="main">

  <div class="page-header">
    <h1>Your Dream Summary</h1>
    <p>Patterns, emotions and keywords across all your entries</p>
  </div>

  <?php if ($total_entries == 0): ?>
  <!-- Empty state -->
  <div class="empty">
    <i class="fa-solid fa-moon"></i>
    <p>No entries yet. Start writing your first dream to see your summary here!</p>
  </div>

  <?php else: ?>

  <!-- ── Stat Cards ── -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon">📓</div>
      <div class="stat-num"><?= $total_entries ?></div>
      <div class="stat-label">Total Entries</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div class="stat-num"><?= count($days) ?></div>
      <div class="stat-label">Active Days (last 7)</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🗓️</div>
      <div class="stat-num" style="font-size:18px;padding-top:10px"><?= $last_entry_fmt ?></div>
      <div class="stat-label">Last Entry</div>
    </div>
  </div>

  <!-- ── Charts Row ── -->
  <div class="charts-grid">
    <!-- Line chart: entries over time -->
    <div class="chart-card">
      <h2>Entries Over Time</h2>
      <p class="chart-sub">How many entries you wrote each day this week</p>
      <div class="chart-wrap">
        <canvas id="lineChart"></canvas>
      </div>
    </div>

    <!-- Doughnut: emotion breakdown -->
    <div class="chart-card">
      <h2>Emotion Breakdown</h2>
      <p class="chart-sub">Distribution detected from your writing</p>
      <div class="chart-wrap">
        <canvas id="doughnutChart"></canvas>
      </div>
    </div>
  </div>

  <!-- ── Emotion Bars ── -->
  <div class="emotion-card">
    <h2>Emotion Tracker</h2>
    <p class="chart-sub">Detected from keywords in your diary entries</p>
    <?php
    $max_score = max(array_values($emotion_scores)) ?: 1;
    ?>
    <div class="emotion-bars">
      <?php foreach ($emotion_scores as $emo => $score): ?>
      <div class="emo-row">
        <span class="emo-name"><?= ucfirst($emo) ?></span>
        <div class="emo-bar-bg">
          <div class="emo-bar-fill"
               style="width:<?= ($score/$max_score)*100 ?>%;background:<?= $emotion_colors[$emo] ?>">
          </div>
        </div>
        <span class="emo-count"><?= $score ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ── Keywords ── -->
  <div class="keywords-card">
    <h2>Top Keywords</h2>
    <p class="chart-sub">Most frequent words appearing across your entries</p>
    <?php if (empty($top_keywords)): ?>
      <p style="color:var(--muted);font-size:13px">Write more entries to see your top keywords!</p>
    <?php else: ?>
    <div class="keyword-cloud">
      <?php
      $kw_colors = [
        ["#e0e4ff","#5a67d8"], ["#fef3c7","#d97706"], ["#dcfce7","#16a34a"],
        ["#fce7f3","#db2777"], ["#ede9fe","#7c3aed"], ["#fff7ed","#ea580c"],
        ["#f0fdf4","#15803d"], ["#fdf4ff","#a21caf"], ["#ecfdf5","#059669"],
        ["#fef9c3","#ca8a04"], ["#f0f9ff","#0284c7"], ["#fff1f2","#be123c"],
      ];
      $i = 0;
      foreach ($top_keywords as $word => $freq):
        $col = $kw_colors[$i % count($kw_colors)];
        $size = max(12, min(20, 12 + $freq * 1.5));
      ?>
      <span class="kw-pill"
            style="background:<?= $col[0] ?>;color:<?= $col[1] ?>;border-color:<?= $col[1] ?>22;font-size:<?= $size ?>px">
        <?= htmlspecialchars($word) ?>
        <sup style="font-size:9px;opacity:.7"><?= $freq ?></sup>
      </span>
      <?php $i++; endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php endif; ?>

  <!-- ── AI Notice ── -->
  <div class="ai-notice">
    <div class="ai-notice-icon">🤖</div>
    <div>
      <h3>AI Analysis Coming in Iteration 2</h3>
      <p>Once the Gemini API is integrated, this page will show deeper AI-generated insights —
         emotion detection, theme extraction, narrative patterns, and personalised reflections
         from your dream entries.</p>
    </div>
  </div>

</main>

<script>
// ── Data from PHP ──────────────────────────────────────────
const days   = <?= json_encode(array_values($days))   ?>;
const counts = <?= json_encode(array_values($counts)) ?>;
const emotions = <?= json_encode($emotion_scores) ?>;
const emoColors = <?= json_encode(array_values($emotion_colors)) ?>;

Chart.defaults.font.family = "'DM Sans', sans-serif";
Chart.defaults.color = "#6b7280";

// ── Line chart ─────────────────────────────────────────────
const lineCtx = document.getElementById('lineChart');
if (lineCtx) {
  new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: days.length ? days : ['No data'],
      datasets: [{
        label: 'Entries',
        data: counts.length ? counts : [0],
        borderColor: '#5a67d8',
        backgroundColor: 'rgba(90,103,216,0.08)',
        borderWidth: 2.5,
        pointBackgroundColor: '#5a67d8',
        pointRadius: 5,
        pointHoverRadius: 7,
        tension: 0.4,
        fill: true,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero:true, ticks:{stepSize:1}, grid:{color:'#f0f2f8'} },
        x: { grid:{display:false} }
      }
    }
  });
}

// ── Doughnut chart ─────────────────────────────────────────
const doughCtx = document.getElementById('doughnutChart');
if (doughCtx) {
  const emoLabels = Object.keys(emotions).map(e => e.charAt(0).toUpperCase() + e.slice(1));
  const emoVals   = Object.values(emotions);
  const hasData   = emoVals.some(v => v > 0);

  new Chart(doughCtx, {
    type: 'doughnut',
    data: {
      labels: emoLabels,
      datasets: [{
        data: hasData ? emoVals : [1,1,1,1,1,1],
        backgroundColor: hasData ? emoColors : ['#e5e7eb','#e5e7eb','#e5e7eb','#e5e7eb','#e5e7eb','#e5e7eb'],
        borderWidth: 0,
        hoverOffset: 8,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '65%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: { boxWidth: 10, padding: 12, font:{size:11} }
        },
        tooltip: { enabled: hasData }
      }
    }
  });
}
</script>

</body>
</html>