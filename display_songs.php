<?php
// display_songs.php
require 'songdatabase.php'; // Your database connection file

// Retrieve selected song IDs from the form
$selected_songs = $_POST['selected_songs'] ?? [];
$selected_order = $_POST['selected_order'] ?? '';

if (empty($selected_songs) || empty($selected_order)) {
    echo "No songs selected.";
    exit;
}

// Preserve order using selected_order
$ids = explode(',', $selected_order);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

// Retrieve full details of the selected songs in correct order
$query = "SELECT * FROM songs WHERE id IN ($placeholders) ORDER BY FIELD(id, $selected_order)";
$stmt = $pdo->prepare($query);
$stmt->execute($ids);
$song_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Selected Songs</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            text-align: center; 
        }
        .song { 
            margin-bottom: 40px; 
        }
        h1, h2 { 
            color: darkblue;
        }
        h3 { 
            color: #2980B9; 
        }
        p { 
            line-height: 1.5; 
        }
        hr { 
            border: 1px solid #ccc; 
            margin: 40px 0; 
        }
        .edit-button {
            display: inline-block;
            padding: 8px 12px;
            margin-top: 10px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .edit-button:hover {
            background-color: #0056b3;
        }
        audio {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Selected Songs</h1>
    <?php foreach($song_details as $song): ?>  <?php if(!empty($song['audio_file'])): ?>
                <h3>Audio:</h3>
                <audio controls>
                    <source src="<?= htmlspecialchars($song['audio_file']) ?>" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            <?php endif; ?>
        <div class="song">
            <h2><?= htmlspecialchars($song['title']) ?></h2>
            <h3>Lyrics:</h3>
            <p><?= nl2br(htmlspecialchars($song['lyrics'])) ?></p>
            <h3>Notes:</h3>
            <p><?= nl2br(htmlspecialchars($song['note'])) ?></p>
            <?php if(!empty($song['minus_one'])): ?>
                <h3>Minus 1 Version:</h3>
                <p><?= nl2br(htmlspecialchars($song['minus_one'])) ?></p>
            <?php endif; ?>
          
            <a class="edit-button" href="edit_song.php?id=<?= $song['id'] ?>">Edit Lyrics</a>
        </div>
        <hr>
    <?php endforeach; ?>
</body>
</html>
