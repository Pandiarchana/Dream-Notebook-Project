<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

$result = $conn->query(
    "SELECT * FROM diary_entries
     WHERE user_id = '$user_id'
     ORDER BY created_at DESC"
);

echo "<h2>Your Diary Entries</h2>";

echo '<div id="chartModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5);">
        <div style="position:absolute; top:50%; left:50%; transform: translate(-50%, -50%); background:white; padding:20px; border-radius:6px; width:80%; max-width:700px;">
            <canvas id="emotionChart"></canvas>
            <button id="closeChart" style="margin-top:10px; padding:5px 10px; background:red; color:white; border:none; cursor:pointer;">Close</button>
        </div>
      </div>';

while ($row = $result->fetch_assoc()) {

    $title = htmlspecialchars($row['title']);
    $content = htmlspecialchars($row['content']);
    $created_at = $row['created_at'];
    $id = $row['id'];

    echo "<h3>{$title}</h3>";
    echo "<small>{$created_at}</small><br>";
    echo "<p>{$content}</p>";

    echo "<a href='edit_form.php?id={$id}'>Edit</a> | ";
    echo "<a href='delete_entry.php?id={$id}' class='delete'>Delete</a><br><br>";
    // Calling Python scripts for emotion analysis
    $process = proc_open(
        "python analyze_emotion_advanced.py",
        [
            0 => ["pipe", "r"], // stdin
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"], // stderr
        ],
        $pipes
    );

    if (is_resource($process)) {
        fwrite($pipes[0], $row['content']);
        fclose($pipes[0]);

        $json = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $err = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($process);

        if ($json) {
            $data = json_decode($json, true);
            if ($data) {
                echo "<p><strong>Overall Emotion:</strong> " . $data['overall_emotion'] . "</p>";
                echo "<p><strong>Risk Level:</strong> " . $data['risk_level'] . "</p>";

                // Sentence-by-sentence analysis
                echo "<details>";
                echo "<summary><strong>Sentence Analysis</strong></summary>";
                foreach ($data['sentence_analysis'] as $sentence) {
                    echo "<p>" . htmlspecialchars($sentence['sentence']) . " → " . $sentence['emotion'] . " (Score: " . $sentence['polarity'] . ")</p>";
                }
                echo "</details>";
                // Prepare data for chart
                $polarityArray = array_map(fn($s) => $s['polarity'], $data['sentence_analysis']);
                $polarityJson = htmlspecialchars(json_encode($polarityArray), ENT_QUOTES, 'UTF-8');
                echo "<button class='show-chart' data-trend='{$polarityJson}'>Show Emotion Chart</button>";

            } else {
                echo "<p><strong>Emotion Analysis:</strong> Error reading result</p>";
            }
        } else {
            echo "<p><strong>Emotion Analysis:</strong> Python script not running</p>";
            if ($err) echo "<p style='color:red;'>Error: $err</p>";
        }
    } else {
        echo "<p><strong>Emotion Analysis:</strong> Failed to start Python process</p>";
    }

    echo "<hr>";
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.querySelectorAll(".show-chart").forEach(btn => {
    btn.addEventListener("click", function(){
        const trend = JSON.parse(this.dataset.trend);
        const ctx = document.getElementById('emotionChart').getContext('2d');

        if(window.chart) window.chart.destroy();
        window.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: trend.map((v,i)=> "Sentence "+(i+1)),
                datasets: [{
                    label: 'Emotion Polarity',
                    data: trend,
                    borderColor: 'blue',
                    tension:0.3,
                    fill:false,
                    pointRadius:5,
                }]
            },
            options: {
                scales: { y: { min: -1, max: 1, title: { display:true, text:'Polarity (-1 → 1)' } } },
                plugins: { legend: { display: true } }
            }
        });

        document.getElementById('chartModal').style.display='block';
    });
});

document.getElementById('closeChart').addEventListener("click", ()=>{
    document.getElementById('chartModal').style.display='none';
});
</script>