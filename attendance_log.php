<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
}

require 'attendance_database.php';

$currentMonth = date('m');
$filter = $_GET['filter'] ?? $currentMonth;
$year = date('Y');
$selectedSunday = $_GET['sunday_filter'] ?? ''; // Sunday filter
$selectedCategory = $_GET['category_filter'] ?? ''; // Category filter

// Fetch all Sundays of the selected month
$sundays = [];
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $filter, $year); // Get correct days in the month
for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = "$year-$filter-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    if (date('N', strtotime($date)) == 7) { // Sunday (7)
        $sundays[] = $date;
    }
}

// Fetch attendance records only registered by the logged-in admin
$query = "SELECT log_id, first_name, last_name, status,category, DATE(timestamp) AS date, remarks 
          FROM attendance_log 
          WHERE YEAR(timestamp) = ? 
          AND MONTH(timestamp) = ? 
          AND admin_id = ?"; // Ensure only the logged-in admin's records are fetched

$params = [$year, $filter, $_SESSION['admin_id']]; // Filter by admin_id

if (!empty($selectedSunday)) {
    $query .= " AND DATE(timestamp) = ?";
    $params[] = $selectedSunday;
}
if (!empty($selectedCategory)) {
    $query .= " AND category = ?";
    $params[] = $selectedCategory;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$attendances = $stmt->fetchAll(PDO::FETCH_ASSOC);

$kids = 0;
$youth = 0;
$adults = 0;

foreach ($attendances as $attendee) {
    if ($attendee['category'] === 'Kid') {
        $kids++;
    } elseif ($attendee['category'] === 'Youth') {
        $youth++;
    } elseif ($attendee['category'] === 'Adult') {
        $adults++;
    }
}

$total = $kids + $youth + $adults;


// Group attendance by user
$user_attendance = [];
foreach ($attendances as $row) {
    $fullName = trim($row['first_name'] . " " . $row['last_name']);
    if (!isset($user_attendance[$fullName])) {
        $user_attendance[$fullName] = [];
    }
    $user_attendance[$fullName][] = $row['date']; // Already formatted as Y-m-d
}

// Identify users with perfect attendance (attended all Sundays)
$perfect_attendance_users = [];
foreach ($user_attendance as $name => $dates) {
    $dates = array_unique($dates); // Remove duplicates
    sort($dates); // Sort dates for consistency
    if (!array_diff($sundays, $dates)) { // If no missing Sundays
        $perfect_attendance_users[] = $name;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Log</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h2, h3 {
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
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #ddd; }
        .perfect-attendance {
            background-color: #ffeb3b;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            color: white;
            border: none;
            cursor: pointer;
            margin: 10px 5px;
            text-decoration: none;
            font-size: 16px;
        }
        .back-btn { background-color: #4CAF50; }
        .back-btn:hover { background-color: #45a049; }
        .print-btn { background-color: #008CBA; }
        .print-btn:hover { background-color: #007bb5; }
        .absent-btn { background-color: #f44336; }
        .absent-btn:hover { background-color: #d32f2f; }
        .perfect-btn { background-color: #ffa500; }
        .perfect-btn:hover { background-color: #ff8c00; }
    </style>
</head>
<body>

<button onclick="window.location.href='dashboard.php';" class="btn back-btn">🔙 Back to Dashboard</button>

<h2>Attendance Log for <?= date("F Y", strtotime("$year-$filter-01")) ?></h2>

<form method="GET">
    <label for="filter">Select Month:</label>
    <select name="filter" id="filter" onchange="this.form.submit()">
        <?php for ($m = 1; $m <= 12; $m++): ?>
            <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= $filter == $m ? 'selected' : '' ?>>
                <?= date("F", mktime(0, 0, 0, $m, 1)) ?>
            </option>
        <?php endfor; ?>
    </select>

    <button type="button" class="btn print-btn" onclick="printAttendance()">🖨 Print Attendance</button>

    <label for="sunday_filter">Select Sunday:</label>
    <select name="sunday_filter" id="sunday_filter" onchange="this.form.submit()">
        <option value="">All Sundays</option>
        <?php foreach ($sundays as $sunday): ?>
            <option value="<?= $sunday ?>" <?= $selectedSunday == $sunday ? 'selected' : '' ?>>
                <?= date("F d, Y", strtotime($sunday)) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <label for="category_filter">Select Category:</label>
    <select name="category_filter" id="category_filter" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <option value="Kid" <?= $selectedCategory == "Kid" ? 'selected' : '' ?>>Kid</option>
        <option value="Youth" <?= $selectedCategory == "Youth" ? 'selected' : '' ?>>Youth</option>
        <option value="Adult" <?= $selectedCategory == "Adult" ? 'selected' : '' ?>>Adult</option>
    </select>
</form>


<h3>Members with Perfect Attendance (<span id="currentMonth"></span>):</h3>
<ul>
    <?php if (empty($perfect_attendance_users)): ?>
        <li>No member have perfect attendance.</li>
    <?php else: ?>
        <?php foreach ($perfect_attendance_users as $user): ?>
            <li class="perfect-attendance">✅ <?= htmlspecialchars($user) ?></li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
<p class="mb-0" style="text-align: center;">
  <strong>Kids:</strong> <?= $kids ?> |
  <strong>Youth:</strong> <?= $youth ?> |
  <strong>Adults:</strong> <?= $adults ?> |
  <strong>Total:</strong> <?= $total ?>
</p>


<table>
    <thead>
        <tr><table id="attendanceTable">

            <th>Name</th>
            <th>Status</th>
            <th>category</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($attendances as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['status']) ?></td>
                <td><?= htmlspecialchars($row['category']) ?></td>
                <td><?= date('F d, Y', strtotime($row['date'])) ?></td>
  </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<button class="btn absent-btn" onclick="window.location.href='absent_list.php?month=<?= $filter ?>&year=<?= $year ?>'">
    View Absentees
</button>

<button class="btn perfect-btn" onclick="window.location.href='perfect_attendance.php?month=<?= $filter ?>&year=<?= $year ?>'">
    View Perfect Attendance
</button>
<script>
function printAttendance() {
    var printContent = document.getElementById("attendanceTable").outerHTML;
    var newWindow = window.open("", "_blank");
    newWindow.document.write(`
        <html>
        <head>
            <title>Print Attendance</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header-container { display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
                .header-container img { width: 120px; height: 120px; margin-right: 15px; }
                .header-text { text-align: left; }
                h2 { margin: 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
                th { background-color: #4CAF50; color: white; }
                tr:nth-child(even) { background-color: #f2f2f2; }
                tr:hover { background-color: #ddd; }
            </style>
        </head>
        <body>
            <div class="header-container">
                <img src="uploads/481151198_1440757323568385_4474186236176026997_n-removebg-preview.png" alt="Logo">
                <div class="header-text">
                    <h2>United Community Kingdom of God Church</h2>
                    <p>Saranay, Cabatuan, Isabela</p>
                </div>
            </div>
            <h2>Attendance for ${new Date().toLocaleString('default', { month: 'long', year: 'numeric' })}</h2>

            ${printContent}
        </body>
        </html>
    `);
    newWindow.document.close();
    newWindow.print();
}
const monthNames = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];
  const currentDate = new Date();
  const currentMonth = monthNames[currentDate.getMonth()];
  document.getElementById("currentMonth").textContent = currentMonth;
</script>


</body>
</html>
