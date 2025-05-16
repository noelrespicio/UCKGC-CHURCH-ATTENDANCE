<?php
session_start();
include 'event_database.php'; // Ensure database connection

// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION["admin_id"];

// Get event ID from URL and ensure it is an integer
$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

if (empty($event_id)) {
    die("<div class='alert alert-danger text-center'>Event not found.</div>");
}

// Fetch event details only if the logged-in admin created it
$event_query = $pdo->prepare("SELECT * FROM events WHERE id = ? AND admin_id = ?");
$event_query->execute([$event_id, $admin_id]);
$event = $event_query->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("<div class='alert alert-danger text-center'>Event not found or you are not authorized to view it.</div>");
}

// Fetch unique addresses for filter dropdown
$address_query = $pdo->prepare("SELECT DISTINCT address FROM attendees a JOIN event_attendance ea ON a.id = ea.attendee_id WHERE ea.event_id = ?");
$address_query->execute([$event_id]);
$addresses = $address_query->fetchAll(PDO::FETCH_COLUMN);

// Fetch attendees with optional address filter
$address_filter = isset($_GET['address']) ? $_GET['address'] : '';
$query = "SELECT a.id, a.first_name, a.last_name, a.address, a.category, ea.timestamp FROM attendees a JOIN event_attendance ea ON a.id = ea.attendee_id WHERE ea.event_id = ?";
$params = [$event_id];

if (!empty($address_filter)) {
    $query .= " AND a.address = ?";
    $params[] = $address_filter;
}

$query .= " ORDER BY ea.timestamp DESC";
$attendees_query = $pdo->prepare($query);
$attendees_query->execute($params);
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
    <script>
        function filterByAddress() {
            const address = document.getElementById('addressFilter').value;
            window.location.href = "?event_id=<?= $event_id ?>&address=" + encodeURIComponent(address);
        }
    </script>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 1000px;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        h2 {
            font-weight: bold;
            color: #343a40;
            margin-bottom: 20px;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .table th, .table td {
            vertical-align: middle;
            padding: 12px;
        }
        .table-responsive {
            margin-top: 20px;
            overflow-x: auto;
        }
        .form-label {
            font-weight: bold;
        }
        .alert-custom {
            background-color: #f1f1f1;
            border-radius: 5px;
            padding: 15px;
            font-size: 16px;
        }
        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-start vh-100">
    <div class="container">
        <a href="events.php" class="btn-back">Back to Events</a>
        <h2 class="text-center">Event: <?= htmlspecialchars($event['event_title']) ?></h2>

        <div class="mb-4">
            <h4 class="mb-3">Attendees List</h4>
               <div class="text-center mb-4">
    <strong>Kids:</strong> <?= $kids ?> | <strong>Youth:</strong> <?= $youth ?> | <strong>Adults:</strong> <?= $adults ?>
</div>
            <div class="form-group">
                <label for="addressFilter" class="form-label">Filter by Address:</label>
                <select id="addressFilter" class="form-select" onchange="filterByAddress()">
                    <option value="">All Addresses</option>
                    <?php foreach ($addresses as $address): ?>
                        <option value="<?= htmlspecialchars($address) ?>" <?= $address_filter == $address ? 'selected' : '' ?>>
                            <?= htmlspecialchars($address) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (!empty($attendees)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Category</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendees as $attendee): ?>
                            <tr>
                                <td><?= htmlspecialchars($attendee['first_name'] . ' ' . $attendee['last_name']) ?></td>
                                <td><?= htmlspecialchars($attendee['address']) ?></td>
                                <td><?= htmlspecialchars($attendee['category']) ?></td>
                                <td><?= htmlspecialchars($attendee['timestamp']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center alert-custom">
                No attendees found for the selected address.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
