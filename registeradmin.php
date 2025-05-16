<?php
session_start();
require_once "database.php";
require 'vendor/autoload.php'; // Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $location = trim($_POST["location"]);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = :email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            $error = "Email is already registered!";
        } else {
            $verification_code = mt_rand(100000, 999999);
            $_SESSION['verification_code'] = $verification_code;
            $_SESSION['temp_email'] = $email;
            $_SESSION['temp_password'] = password_hash($password, PASSWORD_DEFAULT);
            $_SESSION['temp_location'] = $location;

            // Send email verification
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'uckgcsuperad2025@gmail.com'; // Palitan ng iyong Gmail
                $mail->Password = 'jxua ltlb ajjq xabl'; // Palitan ng App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your_email@gmail.com', 'UCKGC Admin');
                $mail->addAddress($email);
                $mail->Subject = 'Your Verification Code';
                $mail->Body = "Your verification code is: $verification_code";

                $mail->send();
                // Redirect after a short delay to show the loading animation
                echo "<script>setTimeout(function() { window.location.href = 'verify.php'; }, 0000);</script>";
                exit();
            } catch (Exception $e) {
                $error = "Email sending failed: " . $mail->ErrorInfo;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin | UCKGC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: url('cover/walpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            backdrop-filter: blur(5px);
            color: #fff;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
        }
        .form-control::placeholder {
            color: #ddd;
        }
        .btn-register {
            background-color: rgba(0, 121, 107, 0.8);
            border: none;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
            width: 100%;
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn-register:hover {
            background-color: rgba(0, 77, 64, 0.8);
            transform: scale(1.05);
        }
        .error-message {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: auto;
            text-align: center;
            padding: 10px;
            background-color: rgba(0, 121, 107, 0.5);
            color: white;
            width: 100%;
            position: fixed;
            bottom: 0;
        }
        /* Loading spinner styles */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
        }
        .loading-spinner div {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner">
        <div></div>
    </div>

    <div class="register-container">
        <h2>Register Admin</h2>
        <?php if (!empty($error)) echo "<p class='error-message'>$error</p>"; ?>
        <form action="registeradmin.php" method="POST" onsubmit="showLoading()">
            <input type="email" class="form-control mb-3" name="email" placeholder="Email" required>
            <input type="password" class="form-control mb-3" name="password" placeholder="Password" required>
            <input type="password" class="form-control mb-3" name="confirm_password" placeholder="Confirm Password" required>
            <input type="text" class="form-control mb-3" name="location" placeholder="Church Location ex. Uckgc-Sampalok, Cabatuan, Isabela" required>
            <button type="submit" class="btn btn-register">Register</button>
            <p class="mt-3">Already have an account? <a href="login.php" class="text-light">Login here</a></p>
        </form>
    </div>

    <div class="footer">
        &copy; 2024 UCKGC Phil. All Rights Reserved.
    </div>

    <script>
        function showLoading() {
            document.getElementById("loadingSpinner").style.display = "block";
        }
    </script>
</body>
</html>
