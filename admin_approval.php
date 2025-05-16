<?php
require_once "database.php";
require 'vendor/autoload.php'; 
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Restrict access to superadmins only
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    header("Location: login.php");
    exit;
}

// Fetch pending users
$stmt = $conn->prepare("SELECT * FROM admins WHERE status = 'pending'");
$stmt->execute();
$pendingUsers = $stmt->fetchAll();

// Success message placeholder
$successMessage = "";

// Function to send email notifications
function sendEmailNotification($toEmail, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; 
        $mail->Password = 'your-app-password'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'Admin Team');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['action'])) {
    $email = $_POST['email'];
    $action = $_POST['action'];

    if ($action == "approve") {
        $updateStmt = $conn->prepare("UPDATE admins SET status = 'approved' WHERE email = :email");
        $updateStmt->bindParam(":email", $email);
        $updateStmt->execute();
        $successMessage = "User <b>$email</b> has been approved!";

        // Send approval email
        $subject = "Your Account is Approved!";
        $message = "<p>Dear User,</p>
                    <p>Congratulations! Your account has been approved. You can now log in.</p>
                    <p>Best Regards,<br>Admin Team</p>";
        sendEmailNotification($email, $subject, $message);
    } else {
        $deleteStmt = $conn->prepare("DELETE FROM admins WHERE email = :email");
        $deleteStmt->bindParam(":email", $email);
        $deleteStmt->execute();
        $successMessage = "User <b>$email</b> has been rejected!";

        // Send rejection email
        $subject = "Your Account Registration was Rejected";
        $message = "<p>Dear User,</p>
                    <p>We regret to inform you that your account registration has been rejected.</p>
                    <p>Best Regards,<br>Admin Team</p>";
        sendEmailNotification($email, $subject, $message);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Approval</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmAction(action, email) {
            document.getElementById('modalAction').value = action;
            document.getElementById('modalEmail').value = email;
            document.getElementById('confirmMessage').innerHTML = 
                `Are you sure you want to <b>${action}</b> user: <b>${email}</b>?`;
            var myModal = new bootstrap.Modal(document.getElementById('confirmModal'));
            myModal.show();
        }

        // Auto-hide alert after 3 seconds
        setTimeout(() => {
            let alert = document.getElementById("success-alert");
            if (alert) alert.style.display = "none";
        }, 3000);
    </script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h3>Pending User Approvals</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($successMessage)): ?>
                    <div id="success-alert" class="alert alert-success text-center">
                        <?= $successMessage ?>
                    </div>
                <?php endif; ?>

                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Email</th>
                            <th>Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pendingUsers) > 0): ?>
                            <?php foreach ($pendingUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['location']) ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm" 
                                        onclick="confirmAction('approve', '<?= $user['email'] ?>')">
                                        Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" 
                                        onclick="confirmAction('reject', '<?= $user['email'] ?>')">
                                        Reject
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">No pending approvals.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="superadmin.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- Modal for confirmation -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmMessage"></p>
                </div>
                <div class="modal-footer">
                    <form method="post">
                        <input type="hidden" name="email" id="modalEmail">
                        <input type="hidden" name="action" id="modalAction">
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
