<?php
session_start();

// Kung walang naka-login, ibalik sa login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bible Games</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f8f9fa;
            padding: 20px;
        }

        h1 {
            font-weight: bold;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .card {
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            border: none;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .icon-circle {
            font-size: 40px;
            color: #4e73df;
        }

        .btn-play {
            margin-top: 10px;
            width: 100%;
        }
    </style>
</head>
<body>

<div class="container text-center">
    <h1>📖 Bible Game Categories</h1>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
        $games = [
            ["Bible Quiz", "bibble_quiz.php", "bi-question-circle-fill"],
            ["Verse Shuffle", "game_verse_shuffle.php", "bi-shuffle"],
            ["Verse Finder", "game_verse_finder.php", "bi-search"],
            ["Bible Speed Round", "game_speed.php", "bi-lightning-charge-fill"],
            ["Books of the Bible", "game_books_order.php", "bi-list-check"],
            ["Bible: True or False", "game_true_false.php", "bi-check2-square"]
        ];

        foreach ($games as $game) {
            echo '<div class="col">
                <div class="card h-100 text-center p-3">
                    <div class="card-body">
                        <div class="icon-circle mb-3"><i class="bi ' . $game[2] . '"></i></div>
                        <h5 class="card-title">' . $game[0] . '</h5>
                        <p class="card-text">Test your knowledge with ' . $game[0] . '!</p>
                        <a href="' . $game[1] . '" class="btn btn-primary btn-play">
                            <i class="bi bi-play-circle me-1"></i>Play
                        </a>
                    </div>
                </div>
            </div>';
        }
        ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
