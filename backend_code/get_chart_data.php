<?php
// get_chart_data.php — Dream Notebook Backend
// Returns chart data for the frontend charts.php page
// Data includes: entries per day (last 7 days), emotion scores, top keywords

session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// ── 1. Entries per day (last 7 days) ────────────────────────
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

$days = [];
$counts = [];
while ($row = $result->fetch_assoc()) {
    $days[]   = date("D d M", strtotime($row['day']));
    $counts[] = (int)$row['count'];
}

// ── 2. Total entries ─────────────────────────────────────────
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM diary_entries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];

// ── 3. Entries per month (last 6 months) ────────────────────
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count
    FROM diary_entries
    WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY MIN(created_at) ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$month_counts = [];
while ($row = $result->fetch_assoc()) {
    $months[]       = $row['month'];
    $month_counts[] = (int)$row['count'];
}

// ── 4. Emotion analysis ──────────────────────────────────────
$stmt = $conn->prepare("SELECT content FROM diary_entries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$all_text = '';
while ($row = $result->fetch_assoc()) {
    $all_text .= ' ' . strtolower($row['content']);
}

$emotion_map = [
    'joy'      => ['happy','joy','excited','great','love','wonderful','amazing','smile','laugh','fun','glad','delight'],
    'fear'     => ['scared','fear','afraid','nightmare','dark','monster','run','chase','terrified','anxious','panic'],
    'stress'   => ['stress','worried','anxious','pressure','late','rush','fail','exam','deadline','nervous','overwhelm'],
    'sadness'  => ['sad','cry','loss','miss','alone','lonely','hurt','pain','grief','tears','lost','broken'],
    'peace'    => ['calm','peaceful','quiet','relax','rest','serene','still','gentle','safe','comfort','dream','float'],
    'surprise' => ['surprise','sudden','unexpected','shock','wow','strange','weird','unusual','suddenly','appeared'],
];

$emotion_scores = [];
foreach ($emotion_map as $emotion => $keywords) {
    $score = 0;
    foreach ($keywords as $keyword) {
        $score += substr_count($all_text, $keyword);
    }
    $emotion_scores[$emotion] = $score;
}

// ── Return all chart data ────────────────────────────────────
echo json_encode([
    'weekly' => [
        'labels' => $days,
        'data'   => $counts,
    ],
    'monthly' => [
        'labels' => $months,
        'data'   => $month_counts,
    ],
    'emotions' => $emotion_scores,
    'total_entries' => (int)$total,
    'status' => 'success'
]);

$conn->close();
?>