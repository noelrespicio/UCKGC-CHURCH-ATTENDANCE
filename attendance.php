<?php
session_start();
require 'attendance-database.php'; // This must define $pdo

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    $attendees_id = $_POST['full_name'];

    // Get the attendee's details based on ID and admin
    $stmt = $pdo->prepare("SELECT first_name, last_name, address, category 
                           FROM attendees 
                           WHERE id = :id AND admin_id = :admin_id");
    $stmt->execute([':id' => $attendees_id, ':admin_id' => $admin_id]);
    $person = $stmt->fetch();

    if ($person) {
        // Check if the person has already marked attendance today
        $stmt_check = $pdo->prepare("SELECT 1 FROM attendance 
                                     WHERE first_name = :first_name 
                                       AND last_name = :last_name 
                                       AND DATE(timestamp) = CURDATE()");
        $stmt_check->execute([
            ':first_name' => $person['first_name'],
            ':last_name' => $person['last_name']
        ]);

        // If attendance already exists for today, prevent further submission
        if ($stmt_check->rowCount() > 0) {
            $message = "U c k g c member have already marked for attendance today.";
        } else {
            // Mark attendance as present
            $timestamp = date('Y-m-d H:i:s');
            $insert = $pdo->prepare("INSERT INTO attendance 
                (first_name, last_name, address, category, status, admin_id, timestamp) 
                VALUES (:first_name, :last_name, :address, :category, 'Present', :admin_id, :timestamp)");
            $insert->execute([
                ':first_name' => $person['first_name'],
                ':last_name' => $person['last_name'],
                ':address' => $person['address'],
                ':category' => $person['category'],
                ':admin_id' => $admin_id,
                ':timestamp' => $timestamp
            ]);

            $message = "Welcome to United Community Kingdom of God Church " . htmlspecialchars($person['first_name']);
        }
    } else {
        $message = "❌ Person not found or unauthorized.";
    }
}


// Get attendees under this admin for the dropdown
$stmt = $pdo->prepare("SELECT id, first_name, last_name FROM attendees WHERE admin_id = :admin_id");
$stmt->execute([':admin_id' => $admin_id]);
$attendees = $stmt->fetchAll();

// Archive old attendance records
try {
    $pdo->beginTransaction();

    // Move old attendance records (older than today) to attendance_log
    $stmt = $pdo->prepare("INSERT INTO attendance_log (first_name, last_name, address, category, status, timestamp, admin_id)
                           SELECT first_name, last_name, address, category, status, timestamp, admin_id FROM attendance 
                           WHERE DATE(timestamp) < CURDATE()");
    $stmt->execute();

    // Delete the moved records
    $deleteStmt = $pdo->prepare("DELETE FROM attendance WHERE DATE(timestamp) < CURDATE()");
    $deleteStmt->execute();

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}

$userData = null;

// QR Code Submission Handling
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["qr_code"])) {
    $qr_code = trim($_POST["qr_code"]);
    $qr_parts = explode("_", $qr_code);

    if (count($qr_parts) == 2) {
        [$first_name, $last_name] = $qr_parts;

        $stmt = $pdo->prepare("SELECT first_name, last_name, address, category FROM attendees WHERE first_name = :first_name AND last_name = :last_name");
        $stmt->execute([":first_name" => $first_name, ":last_name" => $last_name]);

        if ($userData = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $address = $userData['address'];
            $category = $userData['category'];

            // Check if already marked today
            $stmt = $pdo->prepare("SELECT 1 FROM attendance WHERE first_name = :first_name AND last_name = :last_name AND DATE(timestamp) = CURDATE()");
            $stmt->execute([":first_name" => $first_name, ":last_name" => $last_name]);

            if ($stmt->rowCount() == 0) {
                // Insert new attendance
                $insertStmt = $pdo->prepare("INSERT INTO attendance (first_name, last_name, address, category, status, admin_id) 
                                             VALUES (:first_name, :last_name, :address, :category, 'Present', :admin_id)");
                $insertStmt->execute([
                    ":first_name" => $first_name,
                    ":last_name" => $last_name,
                    ":address" => $address,
                    ":category" => $category,
                    ":admin_id" => $admin_id
                ]);
            }

            $message = "<p class='success'>Welcome to United Community Kingdom of God Church, " . htmlspecialchars($first_name) . "!</p>";
        } else {
            $message = "<p class='error'>User not found!</p>";
        }
    } else {
        $message = "<p class='error'>Invalid QR Code!</p>";
    }
}

// Fetch attendance records for this admin
$attendance_stmt = $pdo->prepare("SELECT first_name, last_name, address, category, status, DATE_FORMAT(timestamp, '%Y-%m-%d %r') AS formatted_timestamp 
                                  FROM attendance 
                                  WHERE admin_id = :admin_id 
                                  ORDER BY timestamp DESC");
$attendance_stmt->bindParam(":admin_id", $admin_id);
$attendance_stmt->execute();
$attendance_records = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);

// Count categories
$kids = 0;
$youth = 0;
$adults = 0;
foreach ($attendance_records as $attendance_record) {
    if ($attendance_record['category'] === 'Kid') {
        $kids++;
    } elseif ($attendance_record['category'] === 'Youth') {
        $youth++;
    } elseif ($attendance_record['category'] === 'Adult') {
        $adults++;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Attendance</title>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #eef2f3;
        margin: 0;
        padding: 20px;
        text-align: center;
    }

    .clock {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 15px;
        color: #333;
    }

    .container {
        display: flex;
        flex-direction: column;
        background-color: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        max-width: 100%;
        margin: auto;
        gap: 20px;
    }

    @media (min-width: 768px) {
        .container {
            flex-direction: row;
            max-width: 1200px;
        }

        .left-panel {
            border-right: 2px solid #ddd;
            padding-right: 20px;
        }

        .right-panel {
            padding-left: 20px;
        }
    }

    .left-panel,
    .right-panel {
        flex: 1;
    }

    .left-panel {
        text-align: center;
    }

    #reader {
        width: 100%;
        max-width: 300px;
        margin: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        background-color: #fff;
        margin-top: 10px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        font-size: 14px;
    }

    th, td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #009879;
        color: white;
        text-transform: uppercase;
        font-size: 13px;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    .message-container {
        margin: 10px 0;
        font-size: 15px;
    }

    .success {
        color: #28a745;
    }

    .error {
        color: #dc3545;
    }

    .warning {
        color: #ffc107;
    }

    /* Scrollable table on small screens */
    .table-responsive {
        overflow-x: auto;
    }
</style>

</head>
<body>
<div class="message-container" id="message" data-message="<?= strip_tags($message) ?>"><?= $message ?></div>
     <h1>United Community Kingdom Of God Church</h1>
<div class="clock" id="clock"></div>
    <div class="container">
        <div class="left-panel">
            <h2>📷 QR Code Scanner</h2>
            <div id="reader"></div>
            <input type="text" id="qr-result" placeholder="Scanned QR Code" readonly style="margin-top: 10px; padding: 10px;">
            <form id="qr-form" method="POST">
                <input type="hidden" name="qr_code" id="qr_code">
            </form>
            <div class="message-container"><?= $message ?></div>
            <form id="manual-attendance-form" method="POST" action="">
    <h3>📝 Manual Attendance</h3>
    <label for="full_name" style="display:block; margin-bottom: 5px; font-size: 14px;">Select UCKGC Member</label>
    <select name="full_name" id="full_name" required
            style="padding: 10px; width: 90%; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
        <option value="">-- Select Name --</option>
        <?php foreach ($attendees as $person): ?>
            <option value="<?= htmlspecialchars($person['id']) ?>">
                <?= htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit"
            style="padding: 10px 20px; background-color: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer;">
        ✅ Mark as Present
    </button>
</form>


        </div>
        <div class="right-panel">
            <h2>📋 Attendance List</h2>
            <p class="mb-0"><strong>Kids:</strong> <?= $kids ?> | <strong>Youth:</strong> <?= $youth ?> | <strong>Adults:</strong> <?= $adults ?></p>
            <table>
                <thead>
                    <tr>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Address</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record['first_name']) ?></td>
                            <td><?= htmlspecialchars($record['last_name']) ?></td>
                            <td><?= htmlspecialchars($record['address']) ?></td>
                            <td><?= htmlspecialchars($record['category']) ?></td>
                            <td><?= htmlspecialchars($record['status']) ?></td>
                            <td><?= htmlspecialchars($record['formatted_timestamp']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function updateClock() {
            const now = new Date();
            const dateString = now.toLocaleDateString("en-US", { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
            const timeString = now.toLocaleTimeString("en-US", { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            document.getElementById("clock").innerText = `${dateString} - ${timeString}`;
        }
        setInterval(updateClock, 1000);
        updateClock();
        
        document.addEventListener("DOMContentLoaded", function () {
    const qrResult = document.getElementById("qr-result");
    const qrCodeInput = document.getElementById("qr_code");
    const qrForm = document.getElementById("qr-form");
    const scanner = new Html5Qrcode("reader");

    function startScanner() {
        Html5Qrcode.getCameras()
            .then((devices) => {
                if (devices.length === 0) {
                    alert("No camera found!");
                    return;
                }
                scanner.start(
                    { facingMode: "environment" }, // Use back camera
                    {
                        fps: 30,        // Increase frames per second
                        qrbox: 500,     // Increase scanning area
                        disableFlip: false, // Prevent unnecessary mirroring
                        aspectRatio: 1.5 // Adjust aspect ratio for better detection
                    },
                    (decodedText) => {
                        qrResult.value = decodedText;
                        qrCodeInput.value = decodedText;
                        qrForm.submit();
                    },
                    (errorMessage) => {
                        console.log("QR Scanner Error: ", errorMessage);
                    }
                );
            })
            .catch((err) => {
                alert("Camera access error: " + err);
            });
    }

    startScanner();
});
function speakMessage(text) {
    if ('speechSynthesis' in window) {
        const speech = new SpeechSynthesisUtterance(text);
        speech.lang = 'en-US'; // Set language (change if needed)
        speech.rate = 1.5; // Normal speed
        speech.volume = 1; // Full volume
        speech.pitch = 1.2; // Normal pitch
        window.speechSynthesis.speak(speech);
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const messageElement = document.getElementById("message");
    const messageText = messageElement.getAttribute("data-message");

    if (messageText.trim() !== "") {
        speakMessage(messageText);
    }
});



    </script>
</body>
</html>
