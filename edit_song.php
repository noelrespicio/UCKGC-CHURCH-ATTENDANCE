<?php
// edit_song.php
require 'songdatabase.php'; // Database connection file

// Check if song id is provided
if (!isset($_GET['id'])) {
    echo "No song specified.";
    exit;
}

$song_id = intval($_GET['id']);
$message = '';

// Fetch the song details
$query = "SELECT * FROM songs WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$song_id]);
$song = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$song) {
    echo "Song not found.";
    exit;
}

// Process form submission for editing lyrics and uploading audio
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lyrics = trim($_POST['lyrics']);
    $note = trim($_POST['note']);
    $audio_path = $song['audio_file']; // Keep existing audio file if not replaced

    // Handle file upload if a new file is provided
    if (!empty($_FILES['audio_file']['name'])) {
        $upload_dir = 'uploads/'; // Folder where audio files will be stored
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
        }

        $file_name = time() . "_" . basename($_FILES['audio_file']['name']); // Rename file to prevent duplicates
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $target_path)) {
            $audio_path = $target_path; // Update file path if upload is successful
        } else {
            $message = "<div class='alert alert-danger'>Error uploading audio file.</div>";
        }
    }

    // Validate lyrics
    if (empty($lyrics)) {
        $message = "<div class='alert alert-danger'>Lyrics cannot be empty.</div>";
    } else {
        // Update the database
        $update_query = "UPDATE songs SET lyrics = ?, note = ?, audio_file = ? WHERE id = ?";
        $update_stmt = $pdo->prepare($update_query);
        if ($update_stmt->execute([$lyrics, $note, $audio_path, $song_id])) {
            $message = "<div class='alert alert-success'>Song updated successfully!</div>";

            // Refresh the song details after update
            $stmt->execute([$song_id]);
            $song = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = "<div class='alert alert-danger'>Error updating song. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Song Lyrics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #2C3E50;
        }
        .btn-back {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="lineup_song.php" class="btn btn-secondary btn-back">← Back</a>
        <h1>Edit Lyrics for "<?= htmlspecialchars($song['title']) ?>"</h1>
        <?php echo $message; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="lyrics" class="form-label">Lyrics</label>
                <textarea class="form-control" id="lyrics" name="lyrics" rows="10" required><?= htmlspecialchars($song['lyrics']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="note" class="form-label">Note (Optional)</label>
                <textarea class="form-control" id="note" name="note" rows="4"><?= htmlspecialchars($song['note']) ?></textarea>
            </div>

            <!-- Display Current Audio if Available -->
            <?php if (!empty($song['audio_file'])): ?>
                <div class="mb-3">
                    <label class="form-label">Current Audio</label>
                    <audio controls>
                        <source src="<?= htmlspecialchars($song['audio_file']) ?>" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="audio_file" class="form-label">Upload New Audio File (Optional)</label>
                <input type="file" class="form-control" id="audio_file" name="audio_file" accept="audio/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Song</button>
        </form>
    </div>
</body>
</html>
