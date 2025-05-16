<?php
require_once "database.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["qr_code"])) {
    $qr_code = trim($_POST["qr_code"]);

    // Extract user ID from QR Code (assuming "USER_123")
    if (preg_match('/USER_(\d+)/', $qr_code, $matches)) {
        $userId = $matches[1];

        // Check if the user exists
        $stmt = $conn->prepare("SELECT id FROM attendees WHERE id = :id");
        $stmt->bindParam(":id", $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Insert attendance record
            $stmt = $conn->prepare("INSERT INTO attendance (user_id, timestamp) VALUES (:user_id, NOW())");
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                echo "Attendance recorded for User ID: " . $userId;
            } else {
                echo "Error recording attendance!";
            }
        } else {
            echo "User not found!";
        }
    } else {
        echo "Invalid QR Code format!";
    }
} else {
    echo "Invalid request!";
}
?>
