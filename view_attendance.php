<?php
session_start();
require_once "database.php";

$error = "";
$attendance_data = [];
$password_entered = "";
$selected_month = "";
$selected_date = "";
$admin_found = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $password_entered = trim($_POST["password"]);
    $selected_month = $_POST["month"] ?? "";
    $selected_date = $_POST["date"] ?? "";

    $stmt = $conn->prepare("SELECT * FROM admins");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($admins as $admin) {
        if (password_verify($password_entered, $admin["password"])) {
            $admin_id = $admin["id"];
            $admin_found = true;

            $query = "
                SELECT * FROM attendance_log
                WHERE admin_id = :admin_id
            ";

            if (!empty($selected_month)) {
                $query .= " AND MONTH(timestamp) = :month";
            }

            if (!empty($selected_date)) {
                $query .= " AND DATE(timestamp) = :date";
            }

            $query .= " ORDER BY timestamp DESC";

            $stmt = $conn->prepare($query);
            $stmt->bindParam(":admin_id", $admin_id, PDO::PARAM_INT);

            if (!empty($selected_month)) {
                $stmt->bindParam(":month", $selected_month, PDO::PARAM_INT);
            }

            if (!empty($selected_date)) {
                $stmt->bindParam(":date", $selected_date);
            }

            $stmt->execute();
            $attendance_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($attendance_data)) {
                $error = "No attendance records found for the selected filters.";
            }

            break;
        }
    }

    if (!$admin_found) {
        $error = "Incorrect password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #f5f5f5;
            padding: 40px;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .form-control, .form-select {
            max-width: 300px;
            margin: 10px auto;
        }

        .table {
            margin-top: 30px;
        }

        .alert {
            max-width: 400px;
            margin: 20px auto;
        }

        .btn-group {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container text-center">
    <h2>Admin Attendance Access</h2>

    <form method="POST" class="mt-4">
        <input type="password" name="password" class="form-control" placeholder="Enter Admin Password" required>

        <select name="month" class="form-select">
            <option value="">-- Filter by Month --</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= ($selected_month == $m) ? 'selected' : '' ?>>
                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>

        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($selected_date) ?>">

        <button type="submit" class="btn btn-primary mt-3">View Attendance</button>
    </form>

    <div class="btn-group">
        <a href="login.php" class="btn btn-secondary">Back to Login</a>
        <a href="gamesregister.php" class="btn btn-success">Go to Bible Games</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($attendance_data)): ?>
        <table class="table table-bordered mt-4">
            <thead>
            <tr>
                <th>Attendee Name</th>
                <th>Date & Time</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($attendance_data as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['first_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['timestamp']) ?></td>
                    <td><?= htmlspecialchars($row['category'] ?? $row['status'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
