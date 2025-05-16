<?php
require_once "database.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$error = ''; // Variable to hold error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate a verification code
        $verificationCode = rand(100000, 999999);  // Generate a 6-digit verification code
        $stmt = $conn->prepare("UPDATE admins SET verification_code = :verification_code, token_expiry = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE email = :email");
        $stmt->bindParam(":verification_code", $verificationCode);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        // Send verification code via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  
            $mail->SMTPAuth = true;
            $mail->Username = 'uckgcsuperad2025@gmail.com'; // Palitan ng iyong Gmail
            $mail->Password = 'jxua ltlb ajjq xabl'; // Palitan ng App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('UCKGCadmin@gmail.com', 'UCKGC Admin');
            $mail->addAddress($email); 

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Verification Code';
            $mail->Body    = "Your verification code is: <strong>$verificationCode</strong>";

            $mail->send();
            // Redirect to verification page
            echo "<script>window.location.href = 'verify_code.php?email=$email';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | UCKGC</title>
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
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            text-align: center;
            backdrop-filter: blur(5px);
        }
        .btn-submit {
            background-color: rgba(0, 121, 107, 0.8);
            border: none;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
            width: 100%;
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn-submit:hover {
            background-color: rgba(0, 77, 64, 0.8);
            transform: scale(1.05);
        }
        .error-message {
            color: yellow;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        /* Loading Spinner Styles */
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

    <div class="container">
        <h2>Forgot Password</h2>
        <p>Enter your email to reset your password</p>
        <?php if ($error): ?>
            <p class="error-message"><?= $error ?></p>
        <?php endif; ?>
        <form action="" method="POST" onsubmit="showLoading()">
            <div class="mb-3">
                <input type="email" class="form-control" placeholder="Email" name="email" required>
            </div>
            <button type="submit" class="btn btn-submit">Send Code</button>
        </form>
        <p class="mt-3"><a href="login.php" class="text-light">Back to Login</a></p>
    </div>

    <script>
        function showLoading() {
            document.getElementById("loadingSpinner").style.display = "block";
        }
    </script>
</body>
</html>
