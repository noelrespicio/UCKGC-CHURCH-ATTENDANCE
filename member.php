<?php
session_start();
require_once "database.php"; // Database connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Get attendees
$stmt = $conn->prepare("SELECT id, first_name, last_name, address, category, qr_code FROM attendees WHERE admin_id = :admin_id ORDER BY last_name ASC");
$stmt->execute([':admin_id' => $admin_id]);
$attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Category Counters
$kids = $youth = $adults = 0;
foreach ($attendees as $attendee) {
    switch ($attendee['category']) {
        case 'Kid': $kids++; break;
        case 'Youth': $youth++; break;
        case 'Adult': $adults++; break;
    }
}

// Sort attendees by category: Kid → Youth → Adult
usort($attendees, function ($a, $b) {
    $order = ['Kid' => 1, 'Youth' => 2, 'Adult' => 3];
    return $order[$a['category']] <=> $order[$b['category']];
});

// Get admin location
$stmt = $conn->prepare("SELECT location FROM admins WHERE id = :admin_id");
$stmt->execute([':admin_id' => $admin_id]);
$admin = $stmt->fetch();
$admin_location = $admin['location'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registered Attendees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            max-width: 1000px;
            margin: auto;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .summary {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        #print-section { display: none; }
    </style>
</head>
<body>

<div class="container">
    <!-- Top Buttons -->
    <div class="btn-container">
        <a href="dashboard.php" class="btn btn-dark"><i class="bi bi-arrow-left-circle"></i> Dashboard</a>
        <a href="registermember.php" class="btn btn-primary"><i class="bi bi-person-plus-fill"></i> Register Member</a>
    </div>

    <h2 class="text-center mb-4"><i class="bi bi-people-fill"></i> UCKGC Members</h2>

    <!-- Summary -->
    <div class="summary">
        <span>Kids: <?php echo $kids; ?></span>
        <span>Youth: <?php echo $youth; ?></span>
        <span>Adults: <?php echo $adults; ?></span>
    </div>

    <!-- Print Button -->
    <div class="d-flex justify-content-end mb-2">
        <button onclick="printTable();" class="btn btn-secondary">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
        <!-- Search -->
        <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" id="searchInput" class="form-control" placeholder="Search by name...">
    </div>

    <!-- Attendees Table -->
    <table id="attendeesTable" class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th class="attendee-name">Name</th>
                <th>Address</th>
                <th>Category</th>
                <th>QR Code</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($attendees)): ?>
                <?php foreach ($attendees as $index => $attendee): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td class="attendee-name"><?php echo htmlspecialchars($attendee['first_name'] . " " . $attendee['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($attendee['address']); ?></td>
                        <td><?php echo htmlspecialchars($attendee['category']); ?></td>
                        <td>
                            <?php if (!empty($attendee['qr_code']) && file_exists($attendee['qr_code'])): ?>
                                <img src="<?php echo $attendee['qr_code']; ?>" width="50" alt="QR">
                                <a href="<?php echo $attendee['qr_code']; ?>" download class="btn btn-success btn-sm mt-1">
                                    <i class="bi bi-download"></i> Download
                                </a>
                            <?php else: ?>
                                <span class="text-muted">No QR</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_attendee.php?id=<?php echo $attendee['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No attendees found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Print Section (Hidden) -->
<div id="print-section">
    <div class="text-center mb-4">
        <img src="cover/481151198_1440757323568385_4474186236176026997_n-removebg-preview.png" alt="Logo" width="110">
        <h2 class="mb-0">United Community Kingdom of God Church</h2>
        <p><?php echo htmlspecialchars($admin_location); ?></p>
    </div>

    <div class="summary">
        <span>Kids: <?php echo $kids; ?></span>
        <span>Youth: <?php echo $youth; ?></span>
        <span>Adults: <?php echo $adults; ?></span>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>No.</th>
                <th>Name</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendees as $index => $attendee): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($attendee['first_name'] . " " . $attendee['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($attendee['category']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function printTable() {
    var printContent = document.getElementById('print-section').innerHTML;
    var originalContent = document.body.innerHTML;

    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    location.reload();
}

// Search function
document.getElementById('searchInput').addEventListener('keyup', function () {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#attendeesTable tbody tr');

        rows.forEach(row => {
            let nameCell = row.querySelector('td:nth-child(2)'); // 2nd column = Name
            if (nameCell) {
                let name = nameCell.textContent.toLowerCase();
                if (name.includes(filter)) {
                    row.style.display = '';
                    highlightText(nameCell, filter); // Highlight matching text
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });

    // Highlight matched text
    function highlightText(cell, keyword) {
        let text = cell.textContent;
        let regex = new RegExp(`(${keyword})`, 'gi');
        let newText = text.replace(regex, `<span style="background-color: yellow;">$1</span>`);
        cell.innerHTML = newText;
    }
</script>

</body>
</html>
