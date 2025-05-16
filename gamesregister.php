<?php
require 'gamesdatabase.php';

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $location = trim($_POST['location']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR fullname = ?");
    $stmt->bind_param("ss", $username, $fullname);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Username or Fullname already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, location, username, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $location, $username, $password);
        if ($stmt->execute()) {
            header("Location: bible_games_login.php");
            exit;
        } else {
            $error = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            background: #e0f7fa;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 380px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #00796b;
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
            background: #00796b;
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
    <h2>Create Account(Bibble Games)</h2>
    <form method="POST">
        <p class="error"><?= $error ?></p>
        <label>Full Name</label>
        <input type="text" name="fullname" required>

        <label>Location</label>
        <input type="text" name="location" required>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Register</button>
    </form>
    <div class="link">
        Already have an account? <a href="bible_games_login.php">Login</a>
    </div>
</div>
</body>
</html>
