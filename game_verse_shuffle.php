<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Database connection
$conn = new mysqli("localhost", "root", "", "attendance monitoring system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle score submission and return leaderboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalScore'])) {
    $score = filter_var($_POST['finalScore'], FILTER_VALIDATE_INT);
    $user_id = intval($user['id']);

    if ($score !== false) {
        $stmt = $conn->prepare("SELECT score FROM verse_shuffle_scores WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($existingScore);
        $hasScore = $stmt->fetch();
        $stmt->close();

        if ($hasScore) {
            if ($score > $existingScore) {
                $update = $conn->prepare("UPDATE verse_shuffle_scores SET score = ?, date_played = NOW() WHERE user_id = ?");
                $update->bind_param("ii", $score, $user_id);
                $update->execute();
                $update->close();
            }
        } else {
            $insert = $conn->prepare("INSERT INTO verse_shuffle_scores (user_id, score, date_played) VALUES (?, ?, NOW())");
            $insert->bind_param("ii", $user_id, $score);
            $insert->execute();
            $insert->close();
        }
    }

    // Leaderboard: Top 10
    $leaderboard_query = "
        SELECT u.fullname, u.location, MAX(s.score) AS high_score
        FROM verse_shuffle_scores s
        JOIN users u ON s.user_id = u.id
        GROUP BY u.id
        ORDER BY high_score DESC
        LIMIT 10
    ";
    $result = $conn->query($leaderboard_query);
    $leaderboard = [];

    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = [
            'fullname' => htmlspecialchars($row['fullname']),
            'location' => htmlspecialchars($row['location']),
            'score' => intval($row['high_score'])
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($leaderboard);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verse Shuffle Game</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .container { max-width: 700px; margin-top: 50px; text-align: center; }
        .word-button { margin: 5px; }
        .verse-container { margin: 20px 0; min-height: 50px; }
        .hidden { display: none; }
    </style>
</head>
<body>
<div class="container">
    <h1>Verse Shuffle Game</h1>
    <p>Welcome, <strong><?= htmlspecialchars($user['fullname']); ?></strong>!</p>

    <div id="game-area">
        <p id="instruction">Rearrange the words to form the correct Bible verse:</p>
        <div id="shuffled-words" class="d-flex flex-wrap justify-content-center"></div>
        <div class="verse-container border p-3" id="player-verse"></div>
        <button class="btn btn-warning mb-2" onclick="undoWord()">Undo</button>
        <br>
        <button class="btn btn-success" onclick="submitAnswer()">Submit</button>
    </div>

    <div class="leaderboard hidden" id="leaderboard">
        <h2>Leaderboard (Top 10)</h2>
        <table class="table table-striped">
            <thead><tr><th>Name</th><th>Location</th><th>Score</th></tr></thead>
            <tbody id="leaderboard-body"></tbody>
        </table>
        <button class="btn btn-primary" onclick="startGame()">Play Again</button>
        <a href="bible_games.php" class="btn btn-secondary">Back</a>
    </div>
</div>

<script>
    const currentPlayer = "<?= addslashes($user['fullname']); ?>";
    const verses = [
       { text: "For God so loved the world that he gave his one and only Son", reference: "John 3:16" },
        { text: "I can do all things through Christ who strengthens me", reference: "Philippians 4:13" },
        { text: "The Lord is my shepherd I shall not want", reference: "Psalm 23:1" },
        { text: "Trust in the Lord with all your heart and lean not on your own understanding", reference: "Proverbs 3:5" },
        { text: "In the beginning God created the heavens and the earth", reference: "Genesis 1:1" },
        { text: "Jesus wept", reference: "John 11:35" },
        { text: "Be still and know that I am God", reference: "Psalm 46:10" },
        { text: "Do not be afraid for I am with you", reference: "Isaiah 41:10" },
        { text: "Love your neighbor as yourself", reference: "Mark 12:31" },
        { text: "Give thanks to the Lord for he is good", reference: "Psalm 107:1" },
        { text: "The joy of the Lord is your strength", reference: "Nehemiah 8:10" },
        { text: "God is our refuge and strength", reference: "Psalm 46:1" },
        { text: "Walk by faith not by sight", reference: "2 Corinthians 5:7" },
        { text: "Cast all your anxiety on him because he cares for you", reference: "1 Peter 5:7" },
        { text: "Blessed are the peacemakers", reference: "Matthew 5:9" },
        { text: "Let everything that has breath praise the Lord", reference: "Psalm 150:6" },
        { text: "The Lord is my light and my salvation", reference: "Psalm 27:1" },
        { text: "Do not let your hearts be troubled", reference: "John 14:1" },
        { text: "Commit to the Lord whatever you do", reference: "Proverbs 16:3" },
        { text: "Rejoice always pray continually", reference: "1 Thessalonians 5:16-17" }
        // Add up to 50 as needed
    ];

    let currentVerseIndex = 0;
    let currentVerse = {};
    let score = 0;
    let selectedWords = [];
    let usedButtons = [];

    function shuffleArray(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
        return array;
    }

    function startGame() {
        score = 0;
        currentVerseIndex = 0;
        document.getElementById("leaderboard").classList.add("hidden");
        document.getElementById("game-area").style.display = "block";
        loadVerse();
    }

    function loadVerse() {
        if (currentVerseIndex >= verses.length) {
            currentVerseIndex = 0;
        }

        currentVerse = verses[currentVerseIndex];
        const words = currentVerse.text.split(" ");
        const shuffledWords = shuffleArray([...words]);
        const container = document.getElementById("shuffled-words");
        container.innerHTML = "";
        document.getElementById("player-verse").innerHTML = "";
        selectedWords = [];
        usedButtons = [];

        shuffledWords.forEach(word => {
            const btn = document.createElement("button");
            btn.className = "btn btn-outline-primary word-button";
            btn.textContent = word;
            btn.onclick = () => {
                selectedWords.push(word);
                usedButtons.push(btn);
                btn.disabled = true;
                updatePlayerVerse();
            };
            container.appendChild(btn);
        });
    }

    function updatePlayerVerse() {
        document.getElementById("player-verse").innerText = selectedWords.join(" ");
    }

    function undoWord() {
        if (selectedWords.length > 0) {
            selectedWords.pop();
            const lastBtn = usedButtons.pop();
            lastBtn.disabled = false;
            updatePlayerVerse();
        }
    }

    function submitAnswer() {
        const playerInput = selectedWords.join(" ").trim();
        if (playerInput.toLowerCase() === currentVerse.text.toLowerCase()) {
            score += 1;
            alert("Correct! (" + currentVerse.reference + ")");
            currentVerseIndex++;
            loadVerse();
        } else {
            alert("Incorrect!\nCorrect: " + currentVerse.text + "\nReference: " + currentVerse.reference);
            gameOver();
        }
    }

    function gameOver() {
        fetch("game_verse_shuffle.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "finalScore=" + encodeURIComponent(score)
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById("game-area").style.display = "none";
            const leaderboard = document.getElementById("leaderboard");
            leaderboard.classList.remove("hidden");

            const tbody = document.getElementById("leaderboard-body");
            tbody.innerHTML = "";
            data.forEach(row => {
                const tr = document.createElement("tr");
                tr.innerHTML = `<td>${row.fullname}</td><td>${row.location}</td><td>${row.score}</td>`;
                tbody.appendChild(tr);
            });
        });
    }

    startGame();
</script>

</body>
</html>
