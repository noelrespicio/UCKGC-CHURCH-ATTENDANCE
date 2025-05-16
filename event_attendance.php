<?php
session_start();
include 'event_database.php'; // Ensure database connection

// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

// Get event ID from URL
if (!isset($_GET['event_id']) || empty($_GET['event_id'])) {
    die("Event not found.");
}
$event_id = $_GET['event_id'];

// Fetch event details
$event_query = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$event_query->execute([$event_id]);
$event = $event_query->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Invalid Event.");
}

// Handle QR Code attendance submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data["qr_code"]) || empty(trim($data["qr_code"]))) {
        echo json_encode(["status" => "error", "message" => "Invalid QR Code!"]);
        exit();
    }

    $qr_code = trim($data["qr_code"]);
    $qr_parts = explode("_", $qr_code);

    if (count($qr_parts) !== 2) {
        echo json_encode(["status" => "error", "message" => "Invalid QR Code format!"]);
        exit();
    }

    [$first_name, $last_name] = $qr_parts;

    // Look up attendee by first and last name
    $stmt = $pdo->prepare("SELECT * FROM attendees WHERE first_name = ? AND last_name = ?");
    $stmt->execute([$first_name, $last_name]);
    $attendee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$attendee) {
        echo json_encode(["status" => "error", "message" => "Attendee not found!"]);
        exit();
    }

    // Check if attendee is already marked present
    $check_attendance = $pdo->prepare("SELECT * FROM event_attendance WHERE event_id = ? AND attendee_id = ?");
    $check_attendance->execute([$event_id, $attendee['id']]);

    if (!$check_attendance->fetch(PDO::FETCH_ASSOC)) {
        $insert_attendance = $pdo->prepare("INSERT INTO event_attendance (event_id, attendee_id, timestamp) VALUES (?, ?, NOW())");
        $insert_attendance->execute([$event_id, $attendee['id']]);
    }

    echo json_encode([
        "status" => "success",
        "message" => "Welcome to the United Community Kingdom of God Church " . htmlspecialchars($event['event_title']) . " event!",
        "name" => htmlspecialchars($attendee['first_name'])
    ]);
    exit();
}

// Fetch attendees present for the event
$attendees_query = $pdo->prepare("SELECT a.id, a.first_name, a.last_name, a.address,a.category, ea.timestamp FROM attendees a JOIN event_attendance ea ON a.id = ea.attendee_id WHERE ea.event_id = ? ORDER BY ea.timestamp DESC");
$attendees_query->execute([$event_id]);
$attendees = $attendees_query->fetchAll(PDO::FETCH_ASSOC);

// Count categories
$kids = 0;
$youth = 0;
$adults = 0;
foreach ($attendees as $attendee) {
    if ($attendee['category'] === 'Kid') {
        $kids++;
    } elseif ($attendee['category'] === 'Youth') {
        $youth++;
    } elseif ($attendee['category'] === 'Adult') {
        $adults++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        /* Custom Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            padding: 30px;
        }

        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        h4 {
            font-size: 1.5rem;
            color: #007bff;
            margin-bottom: 15px;
        }

        .btn {
            border-radius: 5px;
        }

        #reader {
            border: 2px solid #007bff;
            border-radius: 10px;
            background-color: #fafafa;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table td {
            vertical-align: middle;
        }

        #scan-result {
            font-size: 1.2rem;
            margin-top: 15px;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .attendee-stats {
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
    <a href="events.php" class="btn btn-secondary mt-3">Back to Events</a>
        <h2 class="text-center">Event Attendance - <?= htmlspecialchars($event['event_title']) ?></h2>

        <div class="row">
            <div class="col-md-5">
                <h4>Scan QR Code</h4>
                <div id="reader" class="mb-3"></div>
                <p id="scan-result" class="fw-bold"></p>
            </div>

            <div class="col-md-7">
                <h4>Attendees List</h4>
                <div class="attendee-stats">
                <div class="text-center mb-4">
    <strong>Kids:</strong> <?= $kids ?> | <strong>Youth:</strong> <?= $youth ?> | <strong>Adults:</strong> <?= $adults ?>
</div>

                </div>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Category</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendees as $attendee): ?>
                        <tr>
                            <td><?= htmlspecialchars($attendee['id']) ?></td>
                            <td><?= htmlspecialchars($attendee['first_name'] . ' ' . $attendee['last_name']) ?></td>
                            <td><?= htmlspecialchars($attendee['address']) ?></td>
                            <td><?= htmlspecialchars($attendee['category']) ?></td>
                            <td><?= htmlspecialchars($attendee['timestamp']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
            </div>
        </div>
    </div>

    <audio id="success-sound" src="success.mp3"></audio>
    <audio id="error-sound" src="error.mp3"></audio>

    <script>
        function speakMessage(message) {
            let speech = new SpeechSynthesisUtterance(message);
            speech.lang = 'en-US';
            speech.volume = 1;
            speech.rate = 1.5;
            speech.pitch = 1;
            window.speechSynthesis.speak(speech);
        }

        function onScanSuccess(decodedText) {
            fetch("event_attendance.php?event_id=<?= $event_id ?>", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ qr_code: decodedText })
            })
            .then(response => response.json())
            .then(data => {
                let result = document.getElementById("scan-result");

                if (data.status === "success") {
                    document.getElementById("success-sound").play();
                    result.innerHTML = `<span class="text-success">${data.message} ${data.name}</span>`;
                    speakMessage(data.message + " " + data.name);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    document.getElementById("error-sound").play();
                    result.innerHTML = `<span class="text-danger">${data.message}</span>`;
                    speakMessage(data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }

        document.addEventListener("DOMContentLoaded", () => {
            let scanner = new Html5QrcodeScanner("reader", { fps: 30, qrbox: 500 });
            scanner.render(onScanSuccess);
        });
    </script>
</body>
</html>
