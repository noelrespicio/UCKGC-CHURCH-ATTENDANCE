<?php
session_start();
require_once "database.php"; 

// Fetch all admins
$stmt = $conn->query("SELECT id, email, location, status FROM admins WHERE role = 'admin'");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Admins</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
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
            <a href="main_admin_dashboard.php" class="btn btn-danger btn-custom">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <form action="admin_approval.php" method="POST">
                <button type="submit" class="btn btn-info btn-custom">
                    <i class="bi bi-person-check"></i> Account Request
                </button>
            </form>
        </div>

        <h2 class="text-center text-primary fw-bold mb-4">Admin / Church List</h2>
        
        <!-- Admins Table -->
        <form action="admin_approval.php" method="POST">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Email</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td class="text-center">
                                <?php if ($admin['status'] === 'pending'): ?>
                                    <input type="checkbox" name="approve_ids[]" value="<?= $admin['id'] ?>">
                                <?php else: ?>
                                    <i class="bi bi-check-circle text-success"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($admin['email']) ?></td>
                            <td><?= htmlspecialchars($admin['location']) ?></td>
                            <td>
                                <span class="badge bg-<?= $admin['status'] === 'pending' ? 'warning' : 'success' ?>">
                                    <?= htmlspecialchars(ucfirst($admin['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_members.php?admin_id=<?= $admin['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-people"></i> Members
                                </a>
                                <a href="adminevents.php?admin_id=<?= $admin['id'] ?>" class="btn btn-success btn-sm">
                                    <i class="bi bi-calendar-event"></i> Events
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

</body>
</html>
