<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';
require_once "database.php"; // Database connection

if (!isset($_SESSION['temp_email'])) {
    die("No email found. Please register first.");
}

$email = $_SESSION['temp_email'];
$mail = new PHPMailer(true);

if (isset($_GET['resend'])) {
    $_SESSION['verification_code'] = rand(100000, 999999);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'uckgcsuperad2025@gmail.com'; 
        $mail->Password = 'jxua ltlb ajjq xabl'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'UCKGC Admin');
        $mail->addAddress($email);
        $mail->Subject = 'Your Verification Code';
        $mail->Body = "Your new verification code is: " . $_SESSION['verification_code'];

        $mail->send();
        echo json_encode(["success" => true]);
        exit;
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $mail->ErrorInfo]);
        exit;
    }
}

$successMessage = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST["verification_code"];
    if ($entered_code == $_SESSION['verification_code']) {
        $stmt = $conn->prepare("INSERT INTO admins (email, password, location) VALUES (:email, :password, :location)");
        $stmt->bindParam(":email", $_SESSION['temp_email']);
        $stmt->bindParam(":password", $_SESSION['temp_password']);
        $stmt->bindParam(":location", $_SESSION['temp_location']);

        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            $successMessage = "Registration successful! Please wait for approval. You will receive an email once your account has been approved.";
        } else {
            $error = "Registration failed.";
        }
    } else {
        $error = "Invalid verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | UCKGC</title>
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
        .verify-container {
            background: rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            text-align: center;
            backdrop-filter: blur(5px);
        }
        .form-control, .btn-verify {
            border-radius: 10px;
            margin-top: 10px;
        }
        .error-message { color: red; }
        .success-message { color: green; }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            padding: 10px;
            background: rgba(0, 121, 107, 0.5);
            color: white;
        }
        .loading-spinner {
            display: none;
            margin: 10px auto;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="verify-container">
    <div class="loading-spinner" id="loading-spinner"></div>
        <h2>Email Verification</h2>
        <?php if (!empty($error)) echo "<p class='error-message'>$error</p>"; ?>
        <form action="" method="POST" id="verify-form">
            <input type="text" class="form-control" name="verification_code" placeholder="Enter Verification Code" required>
            <button type="submit" class="btn btn-success btn-verify">Verify</button>
        </form>
        <p class="mt-3">
            Didn't receive a code? 
            <button id="resend-btn" class="btn btn-link">Resend</button>
        </p>
    </div>
    <div class="footer">&copy; 2024 UCKGC Phil. All Rights Reserved.</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("resend-btn").addEventListener("click", function() {
            var button = this;
            var spinner = document.getElementById("loading-spinner");

            button.disabled = true;
            spinner.style.display = "block";

            fetch("verify.php?resend=true")
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("New verification code sent!");
                } else {
                    alert("Error: " + data.error);
                }
                button.disabled = false;
                spinner.style.display = "none";
            })
            .catch(error => {
                alert("Request failed. Please try again.");
                button.disabled = false;
                spinner.style.display = "none";
            });
        });

        <?php if (!empty($successMessage)) : ?>
            setTimeout(function() {
                alert("<?php echo $successMessage; ?>");
                window.location.href = "login.php";
            }, 500);
        <?php endif; ?>
    </script>
</body>
</html>
