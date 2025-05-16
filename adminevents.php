<?php
session_start();
require_once "database.php";

if (!isset($_GET['admin_id'])) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Admin ID is required.</div></div>");
}

$admin_id = $_GET['admin_id'];

// Fetch admin location
$admin_stmt = $conn->prepare("SELECT location FROM admins WHERE id = ?");
$admin_stmt->execute([$admin_id]);
$admin = $admin_stmt->fetch();

if (!$admin) {
    die("<div class='container mt-5'><div class='alert alert-danger'>Admin not found.</div></div>");
}

// Fetch events created by the admin
$stmt = $conn->prepare("SELECT id, event_title, event_date FROM events WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Events</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .container {
            max-width: 800px;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="superadmin.php" class="btn btn-dark mb-3">
            <i class="bi bi-arrow-left-circle"></i> Back to Admins
        </a>

        <h2 class="text-center mb-4">
            <i class="bi bi-calendar-event-fill"></i> Events of the <span class="text-primary"><?= htmlspecialchars($admin['location']) ?></span>
        </h2>

        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (!empty($events)): ?>
                    <table class="table table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event['event_title']) ?></td>
                                <td><?= date("F j, Y", strtotime($event['event_date'])) ?></td>
                                <td class="text-center">
                                    <a href="superadmin_event_attendees.php?event_id=<?= $event['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-people-fill"></i> View Attendees
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning text-center mb-0">No events found for this church.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
