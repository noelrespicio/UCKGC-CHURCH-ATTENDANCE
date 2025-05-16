<?php
session_start();
require_once "database.php"; // Database connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: attendees.php");
    exit();
}

$attendee_id = $_GET['id'];
$admin_id = $_SESSION['admin_id'];

// Fetch attendee data
$stmt = $conn->prepare("SELECT * FROM attendees WHERE id = :id AND admin_id = :admin_id");
$stmt->execute([':id' => $attendee_id, ':admin_id' => $admin_id]);
$attendee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attendee) {
    header("Location: attendees.php");
    exit();
}

// Handle form submission
$successMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address = $_POST['address'];
    $category = $_POST['category'];

    $update_stmt = $conn->prepare("UPDATE attendees SET first_name = :first_name, last_name = :last_name, address = :address, category = :category WHERE id = :id AND admin_id = :admin_id");
    $update_stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':address' => $address,
        ':category' => $category,
        ':id' => $attendee_id,
        ':admin_id' => $admin_id
    ]);

    $successMessage = "Member updated successfully!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Member</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
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
            max-width: 600px;
            margin: auto;
        }
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #3498db;
            border-top: 5px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div id="loading-overlay">
        <div class="spinner"></div>
    </div>

    <div class="container">
        <h2 class="text-center">Update Member</h2>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success text-center">
                <?php echo $successMessage; ?>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'member.php';
                }, 500);
            </script>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($attendee['first_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($attendee['last_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($attendee['address']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <option value="Kid" <?php echo ($attendee['category'] === 'Kid') ? 'selected' : ''; ?>>Kid</option>
                    <option value="Youth" <?php echo ($attendee['category'] === 'Youth') ? 'selected' : ''; ?>>Youth</option>
                    <option value="Adult" <?php echo ($attendee['category'] === 'Adult') ? 'selected' : ''; ?>>Adult</option>
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <a href="member.php" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</body>
</html>
