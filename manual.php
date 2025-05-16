<?php
session_start();
include 'database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    die("Unauthorized access.");
}


?>

<!DOCTYPE html>
<html>
<head>
    <title>Manual Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        h3 {
            color: #333;
        }

        .message {
            margin-bottom: 15px;
            font-weight: bold;
        }

        form {
            margin-top: 10px;
        }

        select {
            padding: 10px;
            width: 90%;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <h3>📝 Manual Attendance</h3>

    <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="manual.php">
        <select name="full_name" required>
            <option value="">-- Select Name --</option>
            <?php foreach ($attendees as $person): ?>
                <option value="<?= htmlspecialchars($person['id']) ?>">
                    <?= htmlspecialchars($person['first_name'] . ' ' . $person['last_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">✅ Mark as Present</button>
    </form>

</body>
</html>
