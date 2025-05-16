<?php
require_once "database.php";

// Initialize error messages and success message
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'];

    // Hash the new password before storing it
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    try {
        $stmt = $conn->prepare("UPDATE admins SET password = :password WHERE email = :email");
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $success = "Password has been reset successfully! You will be redirected to the login page.";
        
        // Redirect to login page after a brief delay
        echo "<script>
            setTimeout(function() {
                window.location.href = 'login.php';
            }, 3000); // Redirect after 3 seconds
        </script>";
    } catch (Exception $e) {
        $error = "An error occurred while resetting your password. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | UCKGC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: url('cover/walpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #fff;
            font-family: 'Arial', sans-serif;
        }

        .container {
            background: rgba(0, 0, 0, 0.5);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            margin-bottom: 20px;
            color: #f5f5f5;
            text-align: center;
            font-size: 2rem;
        }

        .btn-primary {
            background-color: #28a745;
            border: none;
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        .alert {
            text-align: center;
            font-size: 1rem;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #28a745;
            color: white;
        }

        .alert-danger {
            background-color: #dc3545;
            color: white;
        }

        .form-control {
            border-radius: 5px;
            padding: 12px;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: #28a745;
        }

        .footer-link {
            text-align: center;
            margin-top: 20px;
            color: #f5f5f5;
        }

        .footer-link a {
            color: #28a745;
            text-decoration: none;
        }

        .footer-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Reset Password</h2>

        <!-- Display error message if any -->
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Display success message if password is reset -->
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <!-- Form to enter new password -->
        <?php if (!$success): ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <input type="password" class="form-control" placeholder="New Password" name="new_password" required>
                </div>
                <button type="submit" class="btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>

        <div class="footer-link">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>

</body>
</html>
