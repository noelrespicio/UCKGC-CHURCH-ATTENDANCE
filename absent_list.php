<?php
session_start();
require 'attendance_database.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION["admin_id"];
$filterMonth = $_GET['month'] ?? date('m');
$filterYear = $_GET['year'] ?? date('Y');
$filterSunday = $_GET['sunday'] ?? '';
$nameFilter = $_GET['name'] ?? '';

// Function to get all Sundays in a given month and year
function getSundays($year, $month) {
    $sundays = [];
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    for ($day = 1; $day <= $daysInMonth; $day++) {
        $date = "$year-$month-$day";
        if (date('N', strtotime($date)) == 7) { // 7 = Sunday
            $sundays[] = $date;
        }
    }
    return $sundays;
}

// Get all Sundays of the selected month
$sundays = getSundays($filterYear, $filterMonth);

// Query to fetch absent attendees registered by the logged-in admin
$query_absent = "
    SELECT a.first_name, a.last_name, a.category
    FROM attendees a
    WHERE a.admin_id = ?
    AND NOT EXISTS (
        SELECT 1 FROM attendance_log al
        WHERE al.first_name = a.first_name
        AND al.last_name = a.last_name
        AND YEAR(al.timestamp) = ?
        AND MONTH(al.timestamp) = ?
";

// Parameters for query
$params = [$admin_id, $filterYear, $filterMonth];

// If a specific Sunday is selected, filter by that date
if (!empty($filterSunday)) {
    $query_absent .= " AND DATE(al.timestamp) = ?";
    $params[] = $filterSunday;
}

$query_absent .= ")"; // Close the NOT EXISTS condition

// If a name filter is provided
if (!empty($nameFilter)) {
    $query_absent .= " AND (a.first_name LIKE ? OR a.last_name LIKE ?)";
    $params[] = "%$nameFilter%";
    $params[] = "%$nameFilter%";
}

$stmt_absent = $pdo->prepare($query_absent);
$stmt_absent->execute($params);
$absentees = $stmt_absent->fetchAll(PDO::FETCH_ASSOC);

$kids = 0;
$youth = 0;
$adults = 0;

foreach ($absentees as $attendee) {
    if ($attendee['category'] === 'Kid') {
        $kids++;
    } elseif ($attendee['category'] === 'Youth') {
        $youth++;
    } elseif ($attendee['category'] === 'Adult') {
        $adults++;
    }
}

$total = $kids + $youth + $adults;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absentees List</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        .back-btn, .filter-container button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            cursor: pointer;
        }
        .back-btn:hover, .filter-container button:hover {
            background-color: #45a049;
        }
        .filter-container {
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<button onclick="window.location.href='attendance_log.php'" class="back-btn">🔙 Back to Attendance Log</button>

<h2>Absentees List for <?= date("F Y", strtotime("$filterYear-$filterMonth-01")) ?></h2>

<div class="filter-container">
    <form method="GET" action="">
        <label for="month">Select Month:</label>
        <select name="month" id="month">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= ($filterMonth == str_pad($m, 2, '0', STR_PAD_LEFT)) ? 'selected' : '' ?>>
                    <?= date("F", mktime(0, 0, 0, $m, 1)) ?>
                </option>
            <?php endfor; ?>
        </select>
        
        <label for="year">Select Year:</label>
        <input type="number" name="year" id="year" value="<?= $filterYear ?>" min="2000" max="<?= date('Y') ?>">

        <label for="sunday">Select Sunday:</label>
        <select name="sunday" id="sunday">
            <option value="">All Sundays</option>
            <?php foreach ($sundays as $sunday): ?>
                <option value="<?= $sunday ?>" <?= ($filterSunday == $sunday) ? 'selected' : '' ?>>
                    <?= date("F d, Y", strtotime($sunday)) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="name">Search Name:</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($nameFilter) ?>">
        
        <button type="submit">Filter</button>
    </form>
</div>

<h3>List of Absentees:</h3>
<p class="mb-0" style="text-align: center;">
  <strong>Kids:</strong> <?= $kids ?> |
  <strong>Youth:</strong> <?= $youth ?> |
  <strong>Adults:</strong> <?= $adults ?> |
  <strong>Total:</strong> <?= $total ?>
</p>
<table>
    <thead>
        <tr>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Category</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($absentees) > 0): ?>
            <?php foreach ($absentees as $absentee): ?>

                <tr>
                    <td><?= htmlspecialchars($absentee['first_name']) ?></td>
                    <td><?= htmlspecialchars($absentee['last_name']) ?></td>
                    <td><?= htmlspecialchars($absentee['category']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No absentees found for the selected filters.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
