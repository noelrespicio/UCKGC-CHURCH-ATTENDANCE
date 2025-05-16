<?php
require_once "database.php";

// Initialize error messages
$error = '';
$success = '';

// Initialize the resend button display
$can_resend = false;
$resend_time = 60; // Resend time in seconds (1 minute)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputCode = $_POST['verification_code'];

    // Get email from query parameters (assuming it's passed in the URL)
    $email = $_GET['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Check if the verification code matches and hasn't expired
        if ($user['verification_code'] == $inputCode && strtotime($user['token_expiry']) > time()) {
            // Code is correct and not expired, proceed to password reset
            $success = "Verification successful! Please enter your new password.";
        } else {
            // Incorrect code or expired code
            if (strtotime($user['token_expiry']) <= time()) {
                // Code expired
                $error = "The verification code has expired. Please request a new one.";
                $can_resend = true; // Allow resending the code
            } else {
                // Code mismatch
                $error = "Invalid verification code. Please try again.";
            }
        }
    } else {
        $error = "Email not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Code | UCKGC</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: url('cover/church.jpg') no-repeat center center fixed;
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
        .error-message, .success-message {
            color: yellow;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .countdown {
            font-weight: bold;
            color: #ff9800;
        }
        .resend-link {
            display: none;
            margin-top: 10px;
            color: #4caf50;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Code</h2>
        <p>Enter the verification code sent to your email</p>

        <!-- Display error message if any -->
        <?php if ($error): ?>
            <p class="error-message"><?= $error ?></p>
        <?php endif; ?>

        <!-- Display success message if code is verified -->
        <?php if ($success): ?>
            <p class="success-message"><?= $success ?></p>
            <form action="reset_password.php" method="POST">
                <div class="mb-3">
                    <input type="password" class="form-control" placeholder="New Password" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-submit">Reset Password</button>
            </form>
        <?php else: ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Enter Verification Code" name="verification_code" required>
                </div>
                <button type="submit" class="btn btn-submit">Verify Code</button>
            </form>
            
            <!-- Countdown timer -->
            <div class="countdown" id="countdown"></div>

            <!-- Resend link -->
            <div class="resend-link" id="resend-link">
                <a href="resend_code.php?email=<?= $email ?>" class="text-light">Resend Code</a>
            </div>

        <?php endif; ?>

        <p class="mt-3"><a href="login.php" class="text-light">Back to Login</a></p>
    </div>

    <script>
        // Countdown for resend logic (1 minute)
        let countdown = document.getElementById('countdown');
        let resendLink = document.getElementById('resend-link');

        let timeLeft = <?= $can_resend ? 0 : $resend_time ?>;
        if (timeLeft > 0) {
            countdown.innerHTML = `You can resend the code in ${timeLeft} seconds.`;
            let timer = setInterval(function() {
                timeLeft--;
                countdown.innerHTML = `You can resend the code in ${timeLeft} seconds.`;
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    resendLink.style.display = 'block'; // Show resend link
                }
            }, 1000);
        } else {
            resendLink.style.display = 'block'; // Show resend link if the code expired
        }
    </script>
</body>
</html>
