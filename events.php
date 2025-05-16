<?php
session_start();
include 'event_database.php'; // Siguraduhing tama ang koneksyon sa database

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION["admin_id"];

// Handle event deletion kung may GET parameter na delete_event
if (isset($_GET['delete_event'])) {
    $event_id = $_GET['delete_event'];

    // Tiyaking ang event ay pagmamay-ari ng admin na naka-login
    $sql = "SELECT * FROM events WHERE id = ? AND admin_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_id, $admin_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($event) {
        // I-delete ang event
        $delete_sql = "DELETE FROM events WHERE id = ?";
        $delete_stmt = $pdo->prepare($delete_sql);
        $delete_stmt->execute([$event_id]);

        header("Location: events.php");
        exit();
    } else {
        echo "<script>alert('Event not found or you do not have permission to delete this event!');</script>";
    }
}

// Handle event creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["event_title"])) {
    $event_title = trim($_POST["event_title"]);
    $event_date = date("Y-m-d"); // Awtomatikong kukuhain ang kasalukuyang petsa

    // Tiyaking hindi pa umiiral ang event para sa admin na ito
    $sql = "SELECT * FROM events WHERE event_title = ? AND admin_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$event_title, $admin_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) { // Kung hindi pa umiiral, insert ang bagong event
        $insert_sql = "INSERT INTO events (event_title, event_date, admin_id) VALUES (?, ?, ?)";
        $insert_stmt = $pdo->prepare($insert_sql);
        $insert_stmt->execute([$event_title, $event_date, $admin_id]);
        header("Location: events.php"); // I-refresh ang page para ipakita ang bagong event
        exit();
    } else {
        echo "<script>alert('Event already exists!');</script>";
    }
}

// Kunin ang lahat ng events ng kasalukuyang admin
$sql = "SELECT * FROM events WHERE admin_id = ? ORDER BY event_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$admin_id]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
</head>
<body class="p-5">
    <div class="container">
    <a href="dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>
        <h2 class="mb-4">Create Event</h2>
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label class="form-label">Event Title</label>
                <input type="text" name="event_title" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Event</button>
        </form>

        <h2 class="mb-4">Church Events</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Event ID</th>
                    <th>Event Title</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?= htmlspecialchars($event['id']) ?></td>
                        <td><?= htmlspecialchars($event['event_title']) ?></td>
                        <td><?= htmlspecialchars($event['event_date']) ?></td>
                        <td>
                            <?php 
                            $today = date("Y-m-d"); 
                            if ($event['event_date'] >= $today) { // Attendance open kung ang event ay ngayong araw o sa hinaharap
                            ?>
                                <a href="event_attendance.php?event_id=<?= $event['id'] ?>" class="btn btn-success">Open Attendance</a>
                            <?php } else { // Attendance closed kung lumipas na ang event date ?>
                                <button class="btn btn-secondary" disabled>Attendance Closed</button>
                            <?php } ?>
                            <a href="view_attendees.php?event_id=<?= $event['id'] ?>" class="btn btn-info">View Attendees</a>
                            <a href="events.php?delete_event=<?= $event['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No events found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
