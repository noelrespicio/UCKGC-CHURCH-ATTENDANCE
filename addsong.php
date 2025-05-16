<?php
// Start the session
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

require 'songdatabase.php'; // Ensure the correct path to your database connection file

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $lyrics = trim($_POST["lyrics"]);
    $note = trim($_POST["note"]);
    $audio_file_path = ''; // Will hold the uploaded audio file path
    $admin_id = $_SESSION['admin_id']; // Get the logged-in admin's ID

    // Process audio file upload if one is provided
    if(isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['audio_file']['tmp_name'];
        $fileName = $_FILES['audio_file']['name'];
        $fileSize = $_FILES['audio_file']['size'];
        $fileType = $_FILES['audio_file']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        // Allowed audio file extensions
        $allowedfileExtensions = ['mp3', 'wav', 'ogg'];
        
        if(in_array($fileExtension, $allowedfileExtensions)) {
            // Directory where uploaded files will be stored
            $uploadFileDir = 'uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            // Generate a unique name for the file
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $uploadFileDir . $newFileName;
            
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $audio_file_path = $dest_path;
            } else {
                $message .= "<div class='alert alert-danger'>Error moving the uploaded audio file.</div>";
            }
        } else {
            $message .= "<div class='alert alert-danger'>Upload failed. Allowed file types: " . implode(', ', $allowedfileExtensions) . "</div>";
        }
    }

    // Basic validation: Title and Lyrics are required
    if(empty($title) || empty($lyrics)) {
        $message .= "<div class='alert alert-danger'>Title and Lyrics are required.</div>";
    } else {
        // Prepare the INSERT query (now includes admin_id)
        $query = "INSERT INTO songs (title, lyrics, note, audio_file, admin_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        
        if($stmt->execute([$title, $lyrics, $note, $audio_file_path, $admin_id])) {
            $message .= "<div class='alert alert-success'>Song added successfully!</div>";
        } else {
            $message .= "<div class='alert alert-danger'>Error adding song. Please try again.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Song</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
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
            position: relative;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #2C3E50;
        }
        .back-btn {
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #6c757d;
            color: white;
            padding: 8px 8px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="lineup_song.php" class="back-btn">&#8592; Back to Lineup</a>
        <h1>Add New Song</h1>
        <?php echo $message; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Song Title</label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Enter song title" required>
            </div>
            <div class="mb-3">
                <label for="lyrics" class="form-label">Lyrics</label>
                <textarea class="form-control" id="lyrics" name="lyrics" rows="8" placeholder="Enter song lyrics" required></textarea>
            </div>
            <div class="mb-3">
                <label for="note" class="form-label">Note (Optional)</label>
                <textarea class="form-control" id="note" name="note" rows="3" placeholder="Any additional notes"></textarea>
            </div>
            <div class="mb-3">
                <label for="audio_file" class="form-label">Audio File (Optional)</label>
                <input type="file" class="form-control" id="audio_file" name="audio_file" accept="audio/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Song</button>
        </form>
    </div>
</body>
</html>
