<?php
require 'gamesdatabase.php';
session_start();

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: bible_games.php");
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Username not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            background: #e0f2f1;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 350px;
            margin: 80px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #00695c;
        }
        label {
            display: block;
            margin-top: 10px;
            color: #004d40;
        }
        input[type=text], input[type=password] {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #00695c;
            color: white;
            border: none;
            border-radius: 6px;
            margin-top: 15px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #004d40;
        }
        .link {
            text-align: center;
            margin-top: 12px;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Login(Bibble Games)</h2>
    <form method="POST">
        <p class="error"><?= $error ?></p>
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>
    <div class="link">
        No account yet? <a href="gamesregister.php">Register</a>
    </div>
</div>
</body>
</html>
