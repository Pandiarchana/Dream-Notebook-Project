<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Help & User Guide – Dream Notebook</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,700;0,900;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    :root {
      --blue:#5a67d8; --blue-dk:#434aa8; --blue-lt:#e0e4ff;
      --bg:#f0f2f8; --white:#fff; --text:#1a1a2e; --muted:#6b7280; --border:#e5e7eb;
    }
    body { background:var(--bg); color:var(--text); font-family:'DM Sans',sans-serif; display:flex; min-height:100vh; }

    /* Sidebar */
    .sidebar { width:220px; background:var(--white); border-right:1px solid var(--border); padding:20px; display:flex; flex-direction:column; position:sticky; top:0; height:100vh; flex-shrink:0; }
    .user { display:flex; align-items:center; gap:10px; font-weight:600; margin-bottom:30px; }
    .avatar { width:40px; height:40px; background:var(--blue); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; }
    .add-task { background:var(--blue); color:#fff; border:none; padding:10px; border-radius:6px; cursor:pointer; margin-bottom:20px; font-size:14px; transition:background .2s; text-align:center; text-decoration:none; display:block; }
    .add-task:hover { background:var(--blue-dk); }
    .menu a { display:block; padding:10px; border-radius:6px; text-decoration:none; color:var(--text); margin-bottom:6px; font-size:14px; transition:background .2s; }
    .menu a:hover { background:var(--blue-lt); }
    .menu a.active { background:var(--blue-lt); color:var(--blue); font-weight:600; }
    .sidebar footer { margin-top:auto; font-size:13px; }
    .sidebar footer a { display:block; margin-top:10px; color:var(--muted); text-decoration:none; }
    .sidebar footer a:hover { text-decoration:underline; }

    /* Main */
    .main { flex:1; padding:40px; max-width:860px; }

    .page-header { margin-bottom:40px; }
    .page-header h1 { font-family:'Fraunces',serif; font-size:36px; font-weight:900; margin-bottom:6px; color:var(--text); }
    .page-header p { font-size:15px; color:var(--muted); }

    /* Section */
    .help-section { margin-bottom:36px; }
    .help-section h2 { font-family:'Fraunces',serif; font-size:22px; font-weight:700; margin-bottom:16px; color:var(--blue); display:flex; align-items:center; gap:10px; }

    /* Steps */
    .steps { display:flex; flex-direction:column; gap:12px; }
    .step-item { display:flex; gap:16px; align-items:flex-start; background:var(--white); border-radius:12px; padding:18px 20px; border:1px solid var(--border); }
    .step-num { width:32px; height:32px; background:var(--blue); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-family:'Fraunces',serif; font-weight:700; font-size:15px; flex-shrink:0; }
    .step-body h3 { font-size:15px; font-weight:600; margin-bottom:4px; }
    .step-body p  { font-size:13px; color:var(--muted); line-height:1.6; }

    /* FAQ */
    .faq { display:flex; flex-direction:column; gap:10px; }
    .faq-item { background:var(--white); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .faq-q { padding:16px 20px; font-weight:600; font-size:14px; cursor:pointer; display:flex; justify-content:space-between; align-items:center; user-select:none; }
    .faq-q:hover { background:var(--blue-lt); }
    .faq-q .icon { color:var(--blue); transition:transform .2s; }
    .faq-q.open .icon { transform:rotate(180deg); }
    .faq-a { font-size:13px; color:var(--muted); line-height:1.7; padding:0 20px; max-height:0; overflow:hidden; transition:max-height .3s ease, padding .3s ease; }
    .faq-a.open { max-height:200px; padding:0 20px 16px; }

    /* Tips */
    .tips-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .tip-card { background:var(--white); border-radius:12px; padding:18px 20px; border:1px solid var(--border); }
    .tip-icon { font-size:24px; margin-bottom:8px; }
    .tip-card h3 { font-size:14px; font-weight:600; margin-bottom:4px; }
    .tip-card p  { font-size:13px; color:var(--muted); line-height:1.6; }

    /* Disclaimer */
    .disclaimer { background:var(--blue-lt); border:1px solid rgba(90,103,216,.3); border-radius:12px; padding:20px 24px; display:flex; gap:14px; align-items:flex-start; }
    .disclaimer i { color:var(--blue); font-size:20px; margin-top:2px; flex-shrink:0; }
    .disclaimer p { font-size:13px; color:#374151; line-height:1.7; }

    @media (max-width:768px) {
      .sidebar { display:none; }
      .tips-grid { grid-template-columns:1fr; }
      .main { padding:20px; }
    }
  </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
  <div class="user">
    <div class="avatar"><?= strtoupper($username[0]) ?></div>
    <span><?= htmlspecialchars($username) ?></span>
  </div>
  <a href="write_entry.php" class="add-task">+ Add Entry</a>
  <nav class="menu">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="write_entry.php">✍️ Write Entry</a>
    <a href="history.php">📖 History</a>
    <a href="charts.php">📊 Charts</a>
  </nav>
  <footer>
    <a href="help.php" class="active">❓ Help &amp; resources</a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </footer>
</aside>

<!-- Main -->
<main class="main">

  <div class="page-header">
    <h1>Help & User Guide</h1>
    <p>Everything you need to get the most out of Dream Notebook</p>
  </div>

  <!-- Getting Started -->
  <div class="help-section">
    <h2><i class="fa-solid fa-rocket"></i> Getting Started</h2>
    <div class="steps">
      <div class="step-item">
        <div class="step-num">1</div>
        <div class="step-body">
          <h3>Create an account</h3>
          <p>Click <strong>Sign up</strong> on the home page. Enter your email, choose a username and password. You'll be logged in automatically.</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num">2</div>
        <div class="step-body">
          <h3>Write your first entry</h3>
          <p>On your diary page, enter a title and describe your dream or diary entry in the text box. Click <strong>Save Entry</strong> when done.</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num">3</div>
        <div class="step-body">
          <h3>View your entries</h3>
          <p>Scroll down on your diary page or click <strong>History</strong> in the sidebar to see all your saved dreams and diary entries.</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num">4</div>
        <div class="step-body">
          <h3>Check your Summary</h3>
          <p>Click <strong>Charts</strong> in the sidebar to see charts and emotion trends based on your entries.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Features Guide -->
  <div class="help-section">
    <h2><i class="fa-solid fa-list-check"></i> Features Guide</h2>
    <div class="steps">
      <div class="step-item">
        <div class="step-num"><i class="fa-solid fa-pen" style="font-size:12px"></i></div>
        <div class="step-body">
          <h3>Edit an entry</h3>
          <p>Click the <strong>✏️ Edit</strong> button on any entry card. Update the title or content, then click Save.</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num"><i class="fa-solid fa-trash" style="font-size:12px"></i></div>
        <div class="step-body">
          <h3>Delete an entry</h3>
          <p>Click the <strong>🗑 Delete</strong> button on any entry card. A confirmation dialog will appear before the entry is permanently removed.</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num"><i class="fa-solid fa-chart-line" style="font-size:12px"></i></div>
        <div class="step-body">
          <h3>View your emotion trends</h3>
          <p>The <strong>Charts</strong> page analyses keywords in your entries to detect emotions like joy, fear, stress and peace — shown as charts.</p>
        </div>
      </div>
      <div class="step-item">
        <div class="step-num"><i class="fa-solid fa-right-from-bracket" style="font-size:12px"></i></div>
        <div class="step-body">
          <h3>Logging out</h3>
          <p>Click <strong>Logout</strong> at the bottom of the sidebar. This securely ends your session so no one else can access your entries.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Tips -->
  <div class="help-section">
    <h2><i class="fa-solid fa-lightbulb"></i> Tips for Better Entries</h2>
    <div class="tips-grid">
      <div class="tip-card">
        <div class="tip-icon">🌅</div>
        <h3>Write right after waking</h3>
        <p>Dreams fade quickly. Try to record your dream within the first few minutes of waking up for the most detail.</p>
      </div>
      <div class="tip-card">
        <div class="tip-icon">😊</div>
        <h3>Include emotions</h3>
        <p>Use emotional words like "happy", "scared", "peaceful" — this helps the AI emotion tracker work more accurately.</p>
      </div>
      <div class="tip-card">
        <div class="tip-icon">📅</div>
        <h3>Write regularly</h3>
        <p>The more entries you write, the better your Summary charts become. Even a few sentences each day is enough.</p>
      </div>
      <div class="tip-card">
        <div class="tip-icon">🔑</div>
        <h3>Use descriptive titles</h3>
        <p>Give each entry a meaningful title like "Flying over the ocean" rather than "Dream 1" — it makes entries easier to find later.</p>
      </div>
    </div>
  </div>

  <!-- FAQ -->
  <div class="help-section">
    <h2><i class="fa-solid fa-circle-question"></i> Frequently Asked Questions</h2>
    <div class="faq">
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Is my diary data private? <i class="fa-solid fa-chevron-down icon"></i>
        </div>
        <div class="faq-a">Yes. Your entries are stored securely in the database and are only accessible when you are logged in with your account. No other user can see your entries.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Can I use this as a medical or psychological tool? <i class="fa-solid fa-chevron-down icon"></i>
        </div>
        <div class="faq-a">No. Dream Notebook is designed for personal self-reflection only. The AI analysis and emotion detection are not medically or psychologically validated. Please consult a professional for mental health support.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          How does the emotion detection work? <i class="fa-solid fa-chevron-down icon"></i>
        </div>
        <div class="faq-a">Currently, the system detects emotions by scanning for emotional keywords in your entries (e.g. "happy", "scared", "calm"). In a future iteration, the Gemini AI API will provide deeper and more accurate analysis.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          I forgot my password — what do I do? <i class="fa-solid fa-chevron-down icon"></i>
        </div>
        <div class="faq-a">Password reset functionality will be available in a future update. For now, please contact your system administrator to reset your account.</div>
      </div>
      <div class="faq-item">
        <div class="faq-q" onclick="toggleFaq(this)">
          Can I export my diary entries? <i class="fa-solid fa-chevron-down icon"></i>
        </div>
        <div class="faq-a">Export and backup functionality is planned for Iteration 3 of the project. This will allow you to download your entries as a file for offline storage.</div>
      </div>
    </div>
  </div>

  <!-- Disclaimer -->
  <div class="disclaimer">
    <i class="fa-solid fa-shield-halved"></i>
    <p><strong>Disclaimer:</strong> Dream Notebook is a university project developed for CP3407 Advanced Software Engineering at JCU. The AI-generated analysis is intended for personal self-reflection only and does not constitute medical, psychological, or professional advice. No personal identification data is shared with external AI services.</p>
  </div>

</main>

<script>
  function toggleFaq(el) {
    el.classList.toggle('open');
    el.nextElementSibling.classList.toggle('open');
  }
</script>
</body>
</html>