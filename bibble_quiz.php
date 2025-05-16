<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Database connection (use your credentials here)
$conn = new mysqli("localhost", "root", "", "attendance monitoring system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX score submission & respond with leaderboard JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalScore'])) {
    // Sanitize and validate score input
    $score = filter_var($_POST['finalScore'], FILTER_VALIDATE_INT);
    $user_id = intval($user['id']);

    if ($score !== false) {
        // Check if user already has a score
        $stmt = $conn->prepare("SELECT score FROM scores WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($existingScore);
        $hasScore = $stmt->fetch();
        $stmt->close();

        if ($hasScore) {
            // Update only if new score is higher
            if ($score > $existingScore) {
                $update = $conn->prepare("UPDATE scores SET score = ?, date_played = NOW() WHERE user_id = ?");
                $update->bind_param("ii", $score, $user_id);
                $update->execute();
                $update->close();
            }
        } else {
            // Insert new score record
            $insert = $conn->prepare("INSERT INTO scores (user_id, score, date_played) VALUES (?, ?, NOW())");
            $insert->bind_param("ii", $user_id, $score);
            $insert->execute();
            $insert->close();
        }
    }

    // Get top 10 scores with usernames
    $leaderboard_query = "
        SELECT u.fullname, u.location, MAX(s.score) AS high_score
        FROM scores s
        JOIN users u ON s.user_id = u.id
        GROUP BY u.id
        ORDER BY high_score DESC
        LIMIT 10
    ";
    $result = $conn->query($leaderboard_query);

    $leaderboard = [];
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = ['fullname' => htmlspecialchars($row['fullname']),'location' => htmlspecialchars($row['location']), 'score' => intval($row['high_score'])];
    }

    header('Content-Type: application/json');
    echo json_encode($leaderboard);
    exit;
}

// Quiz questions: [question, correct_answer, [wrong_answers]]
$questions = [
    ["What is the first book of the Bible?", "Genesis", ["Exodus", "Leviticus", "Numbers"]],
    ["Who built the ark?", "Noah", ["Moses", "David", "Abraham"]],
    ["Where was Jesus born?", "Bethlehem", ["Nazareth", "Jerusalem", "Capernaum"]],
    ["Who was swallowed by a big fish?", "Jonah", ["Elijah", "Daniel", "Peter"]],
    ["What did God create on the first day?", "Light", ["Earth", "Water", "Sky"]],
    ["How many days did it rain during the flood?", "40", ["7", "12", "50"]],
    ["Who led the Israelites out of Egypt?", "Moses", ["Aaron", "Joseph", "Joshua"]],
    ["Who was the strongest man in the Bible?", "Samson", ["Goliath", "David", "Saul"]],
    ["What river was Jesus baptized in?", "Jordan", ["Nile", "Euphrates", "Tigris"]],
    ["Who was thrown into the lions' den?", "Daniel", ["Elijah", "Peter", "Paul"]],
    ["Which apostle betrayed Jesus?", "Judas", ["Peter", "John", "Thomas"]],
    ["How many commandments did God give Moses?", "10", ["5", "7", "12"]],
    ["Who was the mother of Jesus?", "Mary", ["Martha", "Elizabeth", "Sarah"]],
    ["What is the shortest verse in the Bible?", "Jesus wept", ["Pray always", "Love one another", "God is love"]],
    ["What is the last book of the Bible?", "Revelation", ["Judges", "Malachi", "Acts"]],
    ["Who wrote most of the Psalms?", "David", ["Solomon", "Asaph", "Moses"]],
    ["What was Paul's original name?", "Saul", ["Simon", "Barnabas", "Stephen"]],
    ["Who was the first king of Israel?", "Saul", ["David", "Solomon", "Rehoboam"]],
    ["What mountain did Moses receive the Ten Commandments?", "Mount Sinai", ["Mount Zion", "Mount Carmel", "Mount Nebo"]],
    ["Who interpreted Pharaoh’s dreams?", "Joseph", ["Daniel", "Moses", "Aaron"]],
    ["Who was the father of John the Baptist?", "Zechariah", ["Joseph", "Elijah", "Samuel"]],
    ["Who was the oldest man mentioned in the Bible?", "Methuselah", ["Noah", "Adam", "Abraham"]],
    ["How many disciples did Jesus have?", "12", ["10", "11", "13"]],
    ["What did Jesus feed to 5,000 people?", "Loaves and fishes", ["Bread only", "Fish only", "Water"]],
    ["Who denied Jesus three times?", "Peter", ["John", "Judas", "Thomas"]],
    ["Where did Jesus perform his first miracle?", "Wedding at Cana", ["Jerusalem", "Nazareth", "Bethlehem"]],
    ["Who wrote the Book of Revelation?", "John", ["Paul", "Peter", "James"]],
    ["What is the first commandment?", "You shall have no other gods before me", ["Remember the Sabbath", "Honor your father and mother", "You shall not steal"]],
    ["Who led the Israelites into the Promised Land?", "Joshua", ["Moses", "Caleb", "Aaron"]],
    ["What is the fruit of the Spirit?", "Love", ["Wealth", "Power", "Knowledge"]],
    ["Who was swallowed by a whale?", "Jonah", ["Elijah", "Noah", "Daniel"]],
    ["How many days did Jesus fast in the wilderness?", "40", ["7", "30", "50"]],
    ["Who betrayed Jesus for 30 pieces of silver?", "Judas", ["Peter", "John", "Thomas"]],
    ["Who was the first woman?", "Eve", ["Sarah", "Mary", "Ruth"]],
    ["What is the Tower of Babel?", "A tower built to reach heaven", ["A city in Egypt", "A mountain", "A temple"]],
    ["Who was the mother of Isaac?", "Sarah", ["Rebekah", "Rachel", "Leah"]],
    ["Who led the army against the Midianites?", "Gideon", ["Samson", "Saul", "David"]],
    ["Who was the prophet taken to heaven in a chariot of fire?", "Elijah", ["Elisha", "Isaiah", "Jeremiah"]],
    ["What was the name of Abraham's wife?", "Sarah", ["Hagar", "Rebekah", "Leah"]],
    ["Who denied Jesus three times before the rooster crowed?", "Peter", ["John", "Judas", "Thomas"]],
    ["Where was Jesus crucified?", "Calvary", ["Golgotha", "Bethlehem", "Nazareth"]],
    ["What did God create on the seventh day?", "He rested", ["Man", "Animals", "Light"]],
    ["Who was the first murderer?", "Cain", ["Abel", "Judas", "Esau"]],
    ["What is the last word in the Bible?", "Amen", ["Hallelujah", "Jesus", "Faith"]],
    ["Who was the disciple known as 'Doubting Thomas'?", "Thomas", ["Peter", "John", "James"]],
    ["What is the Beatitudes?", "Blessings from Jesus’ Sermon on the Mount", ["Laws given to Moses", "Books of Psalms", "Proverbs"]],
    ["Who was the tax collector who climbed a tree to see Jesus?", "Zacchaeus", ["Matthew", "Peter", "Judas"]],
    ["What was Paul's profession?", "Tentmaker", ["Fisherman", "Carpenter", "Shepherd"]]
];

// Shuffle the questions array so the questions appear in a random order
shuffle($questions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Bible Quiz Game</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
    body {
        background: #f4f6f8;
        padding: 20px;
        font-family: Arial, sans-serif;
    }
    .question-box {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: 40px auto;
        text-align: center;
    }
    .answers button {
        margin: 10px 5px;
        width: 100%;
        white-space: normal;
        font-size: 1.1rem;
    }
    .timer {
        font-size: 1.3rem;
        font-weight: bold;
        margin-bottom: 20px;
        color: #d6336c;
    }
    .score-board {
        max-width: 600px;
        margin: 40px auto;
    }
    .leaderboard {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: 40px auto;
        text-align: center;
    }
    h2 {
        margin-bottom: 20px;
        color: #2c3e50;
    }
</style>
</head>
<body>

<div class="question-box" id="quiz-box" aria-live="polite" aria-atomic="true">
    <div class="timer" id="timer">Time: 10</div>
    <h3 id="question-text"></h3>
    <div class="answers" id="answers-container" role="list"></div>
    <div class="score mt-3">Score: <span id="score">0</span></div>
</div>

<div class="leaderboard" id="leaderboard" style="display:none;">
    <h2>Leaderboard (Top 10)</h2>
    <table class="table table-striped">
        <thead><tr><th>Player</th><th>Location</th><th>Score</th></tr></thead>
        <tbody id="leaderboard-body"></tbody>
    </table>
    <button class="btn btn-primary" onclick="restartGame()">Play Again</button>
    <button class="btn btn-primary" onclick="window.location.href='bible_games.php'">Back</button>

</div>

<script>
// Pass PHP questions array to JS
const questions = <?php echo json_encode($questions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

let currentIndex = 0;
let score = 0;
let timeLeft = 10;
let timerInterval;

const questionText = document.getElementById('question-text');
const answersContainer = document.getElementById('answers-container');
const scoreEl = document.getElementById('score');
const timerEl = document.getElementById('timer');
const quizBox = document.getElementById('quiz-box');
const leaderboard = document.getElementById('leaderboard');
const leaderboardBody = document.getElementById('leaderboard-body');

// Shuffle function for answers
function shuffleArray(arr) {
    return arr
        .map(value => ({ value, sort: Math.random() }))
        .sort((a, b) => a.sort - b.sort)
        .map(({ value }) => value);
}

// Timer for each question
function startTimer() {
    timeLeft = 10;
    timerEl.textContent = `Time: ${timeLeft}`;
    timerInterval = setInterval(() => {
        timeLeft--;
        timerEl.textContent = `Time: ${timeLeft}`;
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            gameOver();
        }
    }, 1000);
}

// Load question and answers
function loadQuestion() {
    if (currentIndex >= questions.length) {
        gameOver();
        return;
    }
    const [q, correct, wrongs] = questions[currentIndex];
    questionText.textContent = q;

    // Combine and shuffle answers
    let answers = [...wrongs, correct];
    answers = shuffleArray(answers);

    answersContainer.innerHTML = '';
    answers.forEach(answer => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-primary';
        btn.textContent = answer;
        btn.type = 'button';
        btn.onclick = () => checkAnswer(answer, correct);
        answersContainer.appendChild(btn);
    });

    startTimer();
}

// Check user's answer
function checkAnswer(selected, correct) {
    clearInterval(timerInterval);
    if (selected === correct) {
        score++;
        scoreEl.textContent = score;
        currentIndex++;
        loadQuestion();
    } else {
        gameOver();
    }
}

// End game: hide quiz, show leaderboard
function gameOver() {
    clearInterval(timerInterval);
    quizBox.style.display = 'none';

    // Send score to server and get leaderboard
    fetch('<?= basename(__FILE__) ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `finalScore=${encodeURIComponent(score)}`
    })
    .then(res => res.json())
    .then(data => {
        showLeaderboard(data);
    })
    .catch(err => {
        alert('Error loading leaderboard.');
        console.error(err);
    });
}

// Render leaderboard table
function showLeaderboard(data) {
    leaderboardBody.innerHTML = '';
    data.forEach(item => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${item.fullname}</td><td>${item.location}</td><td>${item.score}</td>`;
        leaderboardBody.appendChild(tr);
    });
    leaderboard.style.display = 'block';
}

// Restart game from beginning
function restartGame() {
    currentIndex = 0;
    score = 0;
    scoreEl.textContent = score;
    leaderboard.style.display = 'none';
    quizBox.style.display = 'block';
    loadQuestion();
}

loadQuestion();

</script> 
</body> 
</html>