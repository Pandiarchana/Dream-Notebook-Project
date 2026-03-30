<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

$title   = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$mood    = $_POST['mood'] ?? '';

if ($title === '' || $content === '') {
    die("Entry cannot be empty");
}

// save the diary.
$sql = "INSERT INTO diary_entries (user_id, title, content, mood)
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $_SESSION['user_id'], $title, $content, $mood);

if ($stmt->execute()) {

    // Retrieve the ID of the newly inserted entry.
    $entry_id = $stmt->insert_id;

    // Call Python sentiment analysis
    $escaped = escapeshellarg($content);
    $command = "python analyze_emotion_advanced.py $escaped";
    $emotion_result = shell_exec($command);

    if ($emotion_result) {
        // Assuming Python returns JSON format
        // { "polarity":0.5, "subjectivity":0.4, "sentence_analysis":[...], "overall_emotion":"Joy", "risk_level":"low" }
        $data = json_decode($emotion_result, true);

        $polarity           = $data['polarity'] ?? 0;
        $subjectivity       = $data['subjectivity'] ?? 0;
        $sentence_analysis  = json_encode($data['sentence_analysis'] ?? []);
        $overall_emotion    = $data['overall_emotion'] ?? '';
        $risk_level         = $data['risk_level'] ?? 'low';

        // Store the analysis results
        $sql2 = "INSERT INTO emotion_analysis 
                 (entry_id, polarity, subjectivity, sentence_analysis, overall_emotion, risk_level)
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("iddsss", $entry_id, $polarity, $subjectivity, $sentence_analysis, $overall_emotion, $risk_level);
        $stmt2->execute();
    }

    // Navigate to the History page
    header("Location: history.php");
    exit();

} else {
    die("Failed to save entry");
}
?>