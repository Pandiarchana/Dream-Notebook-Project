<?php
// analyze_emotion.php — Dream Notebook Backend
// Analyzes diary entry text and returns detected emotions and keywords
// Called by charts.php and summary.php to get emotion data

session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];

// ── Emotion keyword map ──────────────────────────────────────
$emotion_map = [
    'joy'      => ['happy','joy','excited','great','love','wonderful','amazing','smile','laugh','fun','glad','delight','cheerful','celebrate'],
    'fear'     => ['scared','fear','afraid','nightmare','dark','monster','run','chase','terrified','anxious','panic','horror','dread','threat'],
    'stress'   => ['stress','worried','anxious','pressure','late','rush','fail','exam','deadline','nervous','overwhelm','tense','burden','exhaust'],
    'sadness'  => ['sad','cry','loss','miss','alone','lonely','hurt','pain','grief','tears','lost','broken','sorrow','regret','despair'],
    'peace'    => ['calm','peaceful','quiet','relax','rest','serene','still','gentle','safe','comfort','dream','float','tranquil','harmony','relief'],
    'surprise' => ['surprise','sudden','unexpected','shock','wow','strange','weird','unusual','suddenly','appeared','astonish','discover','reveal'],
];

// ── Get all entries for user ─────────────────────────────────
$stmt = $conn->prepare("SELECT content FROM diary_entries WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$all_text = '';
while ($row = $result->fetch_assoc()) {
    $all_text .= ' ' . strtolower($row['content']);
}

// ── Count emotion scores ─────────────────────────────────────
$emotion_scores = [];
foreach ($emotion_map as $emotion => $keywords) {
    $score = 0;
    foreach ($keywords as $keyword) {
        $score += substr_count($all_text, $keyword);
    }
    $emotion_scores[$emotion] = $score;
}

// ── Extract top keywords ─────────────────────────────────────
$stop_words = ['the','a','an','and','or','but','in','on','at','to','for','of','with',
               'is','was','i','my','me','it','this','that','he','she','they','we',
               'had','have','be','so','up','do','if','as','by','no','not','are','from',
               'his','her','its','our','your','their','what','which','who','when',
               'where','how','all','been','were','will','would','could','should','very',
               'just','then','than','about','into','like','more','also','after','before'];

$words = str_word_count(strtolower($all_text), 1);
$filtered = array_filter($words, fn($w) => !in_array($w, $stop_words) && strlen($w) > 3);
$freq = array_count_values($filtered);
arsort($freq);
$top_keywords = array_slice($freq, 0, 10, true);

// ── Return JSON response ─────────────────────────────────────
echo json_encode([
    'emotions'     => $emotion_scores,
    'keywords'     => $top_keywords,
    'total_words'  => count($words),
    'status'       => 'success'
]);

$conn->close();
?>