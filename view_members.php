<?php
session_start();
require_once "database.php"; 

if (!isset($_GET['admin_id'])) {
    die("<div class='alert alert-danger text-center'>Admin ID not provided.</div>");
}

$admin_id = $_GET['admin_id'];

// Get admin info
$stmtAdmin = $conn->prepare("SELECT email, location FROM admins WHERE id = :admin_id");
$stmtAdmin->execute([':admin_id' => $admin_id]);
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("<div class='alert alert-warning text-center'>Admin not found.</div>");
}

// Fetch members added by this admin
$stmt = $conn->prepare("SELECT id, first_name, last_name, address, category, qr_code FROM attendees WHERE admin_id = :admin_id ORDER BY last_name ASC");
$stmt->execute([':admin_id' => $admin_id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count categories
$kids = 0;
$youth = 0;
$adults = 0;
foreach ($members as $member) {
    if ($member['category'] === 'Kid') {
        $kids++;
    } elseif ($member['category'] === 'Youth') {
        $youth++;
    } elseif ($member['category'] === 'Adult') {
        $adults++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members by Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background: #f8f9fa;
        }
        .container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .qr-container img {
            border-radius: 5px;
            transition: transform 0.2s;
        }
        .qr-container img:hover {
            transform: scale(1.2);
        }
    </style>
</head>
<body>

<div class="container mt-5">

<div class="mt-4 p-3 bg-light d-flex justify-content-between align-items-center rounded">
    <a href="superadmin.php" class="btn btn-dark">
        <i class="bi bi-arrow-left-circle"></i> Back to Admin List
    </a>
    <p class="mb-0"><strong>Kids:</strong> <?= $kids ?> | <strong>Youth:</strong> <?= $youth ?> | <strong>Adults:</strong> <?= $adults ?></p>
</div>

    
    <h2 class="text-center mb-4 text-primary">
        <i class="bi bi-people-fill"></i> Members of <?= htmlspecialchars($admin['location']) ?>
    </h2>
    
    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Category</th>
                    <th>QR Code</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($members) > 0): ?>
                    <?php foreach ($members as $index => $member): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td class="fw-bold"> <?= htmlspecialchars($member['first_name'] . " " . $member['last_name']); ?> </td>
                            <td><?= htmlspecialchars($member['address']); ?></td>
                            <td><?= htmlspecialchars($member['category']); ?></td>
                            <td>
                                <?php if (!empty($member['qr_code']) && file_exists($member['qr_code'])): ?>
                                    <div class="qr-container text-center">
                                        <img src="<?= $member['qr_code']; ?>" alt="QR Code" width="50">
                                        <br>
                                        <a href="<?= $member['qr_code']; ?>" download class="btn btn-success btn-sm mt-2">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">No QR</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-danger">No members found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>