<?php
session_start();
require_once "database.php"; 

if (!isset($_GET['event_id'])) {
    die("Event ID is required.");
}

$event_id = $_GET['event_id'];

// Fetch event details
$stmt = $conn->prepare("SELECT event_title FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event not found.");
}

// Fetch attendees of the event
$stmt = $conn->prepare("
    SELECT a.id, a.first_name, a.last_name, a.address,a.category, ea.timestamp 
    FROM attendees a 
    JOIN event_attendance ea ON a.id = ea.attendee_id 
    WHERE ea.event_id = ?
");
$stmt->execute([$event_id]);
$attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendees</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            border-radius: 50px;
            font-weight: 600;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            opacity: 0.8;
        }
        .table thead {
            background: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background: rgba(0, 123, 255, 0.1);
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="javascript:history.back()" class="btn btn-secondary btn-custom">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <h2 class="text-center text-primary fw-bold mb-4">
            Attendees for <strong><?= htmlspecialchars($event['event_title']) ?></strong>
        </h2>

        <!-- Attendees Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Category</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendees)): ?>
                        <?php foreach ($attendees as $attendee): ?>
                        <tr>
                            <td><?= htmlspecialchars($attendee['first_name'] . ' ' . $attendee['last_name']) ?></td>
                            <td><?= htmlspecialchars($attendee['address']) ?></td>
                            <td><?= htmlspecialchars($attendee['category']) ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($attendee['timestamp']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">
                                <i class="bi bi-person-x"></i> No attendees found for this event.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
