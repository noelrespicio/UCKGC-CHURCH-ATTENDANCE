<?php
session_start();
require 'songdatabase.php'; // Database connection

// Redirect kung hindi naka-login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION["admin_id"]; // Kuhanin ang admin ID ng naka-login

// Get search query kung meron
$search = $_GET['search'] ?? '';

// Kunin ang songs ng naka-login na admin
$query = "SELECT id, title FROM songs WHERE title LIKE ? AND admin_id = ? ORDER BY title ASC";
$stmt = $pdo->prepare($query);
$stmt->execute(['%' . $search . '%', $admin_id]);
$songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Christian Praise & Worship Songs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .container {
            max-width: 700px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .song-item {
            transition: background 0.3s;
        }
        .song-item:hover {
            background: #f1f1f1;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const orderArray = [];
            const checkboxes = document.querySelectorAll('input[name="selected_songs[]"]');
            const orderInput = document.getElementById('selected_order');

            checkboxes.forEach(chk => {
                chk.addEventListener('change', function() {
                    if (this.checked) {
                        if (orderArray.length >= 4) {
                            this.checked = false;
                            alert("You can select a maximum of 4 songs.");
                            return;
                        }
                        orderArray.push(this.value);
                    } else {
                        const index = orderArray.indexOf(this.value);
                        if (index !== -1) {
                            orderArray.splice(index, 1);
                        }
                    }
                    orderInput.value = orderArray.join(',');
                });
            });
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">Christian Praise & Worship Songs</h2>
            <a href="addsong.php" class="btn btn-success">+ Add Song</a>
        </div>

        <!-- Search Form -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search songs..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
            </div>
        </form>

        <!-- Song Selection List -->
        <form method="POST" action="display_songs.php">
            <input type="hidden" name="selected_order" id="selected_order" value="">
            <div class="list-group">
                <?php if(count($songs) > 0): ?>
                    <?php foreach($songs as $song): ?>
                        <label class="list-group-item d-flex align-items-center song-item">
                            <input type="checkbox" name="selected_songs[]" value="<?= $song['id'] ?>" class="form-check-input me-2">
                            <?= htmlspecialchars($song['title']) ?>
                        </label>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">No songs found.</div>
                <?php endif; ?>
            </div>
            <div class="mt-4 d-flex justify-content-between">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <button type="submit" class="btn btn-primary">Display Selected Songs</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>