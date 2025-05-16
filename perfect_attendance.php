<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require 'attendance_database.php'; // Ensure this contains $pdo database connection

$admin_id = $_SESSION["admin_id"]; // Get the logged-in admin's ID
$currentMonth = date('m');
$filter = $_GET['filter'] ?? $currentMonth;
$year = date('Y');

// Get the correct number of days in the selected month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $filter, $year);

// Get all Sundays of the selected month
$sundays = [];
for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = "$year-$filter-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    if (date('N', strtotime($date)) == 7) { // 7 = Sunday
        $sundays[] = $date;
    }
}

$perfect_attendees = [];
if (!empty($sundays)) {
    $placeholders = implode(',', array_fill(0, count($sundays), '?'));
    
    $query = "SELECT first_name, last_name, address, COUNT(DISTINCT DATE(timestamp)) as total_sundays 
              FROM attendance_log
              WHERE YEAR(timestamp) = ? AND MONTH(timestamp) = ? 
              AND DATE(timestamp) IN ($placeholders)
              AND admin_id = ?
              GROUP BY first_name, last_name, address
              HAVING total_sundays = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute(array_merge([$year, $filter], $sundays, [$admin_id, count($sundays)]));
    $perfect_attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Attendance Log</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function showPerfectAttendancePopup(name, month) {
            alert(name + " has perfect attendance for " + month + "!");
            window.location.href = 'generate_cert.php?name=' + encodeURIComponent(name) + '&month=' + encodeURIComponent(month);
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            text-align: center;
        }
        select, button {
            padding: 10px;
            margin: 10px;
            font-size: 16px;
        }
        .perfect-attendance {
            background: #dff0d8;
            padding: 10px;
            margin: 10px auto;
            width: 50%;
            border-radius: 5px;
        }
        .back-button {
            background: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }
        .back-button:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>
<?php if (isset($_SESSION['admin_id'])): ?>
    <h2>Perfect Attendance for <?= date("F Y", strtotime("$year-$filter-01")) ?></h2>

    <form method="GET">
        <label for="filter">Select Month:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= ($filter == str_pad($m, 2, '0', STR_PAD_LEFT)) ? 'selected' : '' ?>>
                    <?= date("F", mktime(0, 0, 0, $m, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>
    </form>

    <h3>Attendees with Perfect Attendance:</h3>
    <ul>
        <?php if (empty($perfect_attendees)): ?>
            <li>No attendees with perfect attendance.</li>
        <?php else: ?>
            <?php foreach ($perfect_attendees as $attendee): ?>
                <li class="perfect-attendance">
                    ✅ <?= htmlspecialchars($attendee['first_name'] . ' ' . $attendee['last_name']) ?> (<?= htmlspecialchars($attendee['address']) ?>)
                    <button onclick="showPerfectAttendancePopup('<?= htmlspecialchars($attendee['first_name'] . ' ' . $attendee['last_name']) ?>', '<?= date("F", mktime(0, 0, 0, $filter, 1)) ?>')">
                        🎖 Download Certificate
                    </button>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
<?php else: ?>
    <p>Access denied. Only administrators can view this page.</p>
<?php endif; ?>

<button class="back-button" onclick="window.location.href='dashboard.php'">⬅ Back</button>

</body>
</html>
