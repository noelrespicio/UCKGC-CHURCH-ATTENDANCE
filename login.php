<?php
session_start();
require_once "database.php";

if (!isset($_SESSION["login_attempts"])) {
    $_SESSION["login_attempts"] = 0;
}

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();
    $user = $stmt->fetch();
    if ($user) {
        if ($user["status"] === "pending") {
            $error = "Your account is pending approval. Please wait for confirmation.";
        } elseif (password_verify($password, $user["password"])) {
            $_SESSION["admin_id"] = $user["id"];
            $_SESSION["admin_email"] = $user["email"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["login_attempts"] = 0;

            header("Location: " . ($user["role"] === "superadmin" ? "main_admin_dashboard.php" : "dashboard.php"));
            exit();
        } else {
            $_SESSION["login_attempts"]++;
            $error = "Incorrect password!";
        }
    } else {
        $_SESSION["login_attempts"]++;
        $error = "Invalid email or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | UCKGC</title>
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

        .login-container {
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
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #fff;
        }

        .form-control::placeholder {
            color: #bbb;
        }

        .btn-login {
            background: linear-gradient(45deg, #00d9a5, #00796b);
            border: none;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
            width: 100%;
            transition: 0.3s ease-in-out;
        }

        .btn-login:hover {
            background: linear-gradient(45deg, #00796b, #004d40);
            transform: translateY(-2px);
        }

        .btn-attendance {
            background: linear-gradient(45deg, #42a5f5, #1e88e5);
            border: none;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
            width: 100%;
            display: inline-block;
            text-align: center;
            text-decoration: none;
            transition: 0.3s ease-in-out;
        }

        .btn-attendance:hover {
            background: linear-gradient(45deg, #1e88e5, #1565c0);
            transform: translateY(-2px);
            color: #fff;
        }

        .alert-custom {
            background: rgba(255, 0, 0, 0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: bold;
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

        a {
            color: #00d9a5;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
            color: #00796b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (!empty($error)) { ?>
            <div class="alert-custom"><?= htmlspecialchars($error) ?></div>
        <?php } ?>

        <form action="login.php" method="POST">
            <div class="mb-3">
                <input type="email" class="form-control" placeholder="Email" name="email" required>
            </div>
            <div class="mb-3">
                <input type="password" class="form-control" placeholder="Password" name="password" required>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
        </form>

        <?php if ($_SESSION["login_attempts"] >= 3) { ?>
            <p class="mt-2"><a href="forgot_password.php">Forgot Password?</a></p>
        <?php } ?>

        <p class="mt-3">Don't have an account? <a href="registeradmin.php">Register here</a></p>

        <div class="mt-3">
            <a href="view_attendance.php" class="btn btn-attendance">View Attendance</a>
        </div>
    </div>

    <div class="footer">
        &copy; 2024 UCKGC Phil. All Rights Reserved.
    </div>
</body>
</html>
