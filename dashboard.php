<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | UCKGC</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        background: url('cover/church.jpg') no-repeat center center fixed;
        background-size: cover;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        color: #fff;
        box-sizing: border-box;
    }

    .dashboard-container {
        background: rgba(255, 255, 255, 0.9);
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        max-width: 600px;
        width: 100%;
        text-align: center;
    }

    .dashboard-container h2 {
        margin-bottom: 30px;
        font-weight: bold;
        color: #333;
        font-size: 2rem;
    }

    .btn-custom {
        width: 100%;
        padding: 14px;
        font-size: 1.1rem;
        border: none;
        border-radius: 8px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        font-weight: bold;
        text-transform: uppercase;
        cursor: pointer;
    }

    .btn-custom i {
        margin-right: 8px;
    }

    .btn-register { background-color: #007bff; color: #fff; }
    .btn-register:hover { background-color: #0056b3; transform: translateY(-2px); }

    .btn-attendance { background-color: #28a745; color: #fff; }
    .btn-attendance:hover { background-color: #1e7e34; transform: translateY(-2px); }

    .btn-attendance-log { background-color: #ffc107; color: #333; }
    .btn-attendance-log:hover { background-color: #e0a800; transform: translateY(-2px); }

    .btn-lineup-song { background-color: #6f42c1; color: #fff; }
    .btn-lineup-song:hover { background-color: #593196; transform: translateY(-2px); }

    .btn-members { background-color: #17a2b8; color: #fff; }
    .btn-members:hover { background-color: #117a8b; transform: translateY(-2px); }

    .btn-logout { background-color: #dc3545; color: #fff; }
    .btn-logout:hover { background-color: #b02a37; transform: translateY(-2px); }

    /* Floating Action Button */
    .fab-tutorial {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #28a745;
        color: #fff;
        padding: 15px;
        border-radius: 50%;
        font-size: 1.5rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        z-index: 1000;
    }

    /* Modal Styling */
    .modal-content {
        padding: 20px;
        background-color: rgb(141, 164, 187);
    }

    .modal-header {
        background-color: #28a745;
        color: #fff;
        border-bottom: 2px solid #28a745;
    }

    /* Responsive Styles */
    @media (max-width: 576px) {
        .dashboard-container {
            padding: 25px 20px;
        }

        .dashboard-container h2 {
            font-size: 1.5rem;
        }

        .btn-custom {
            font-size: 1rem;
            padding: 12px;
        }

        .fab-tutorial {
            padding: 12px;
            font-size: 1.3rem;
        }
    }
</style>

</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome, Admin</h2>
        <a href="registermember.php" class="btn btn-custom btn-register">
            <i class="fas fa-user-plus"></i> Register Member
        </a>
        <a href="attendance.php" class="btn btn-custom btn-attendance">
            <i class="fas fa-qrcode"></i> Members Attendance (Sunday)
        </a>
        <a href="events.php" class="btn btn-custom btn-attendance">
            <i class="fas fa-calendar-check"></i> UCKGC Events
        </a>
        <a href="attendance_log.php" class="btn btn-custom btn-attendance-log">
            <i class="fas fa-list"></i> View Attendance Log
        </a>
        <a href="lineup_song.php" class="btn btn-custom btn-lineup-song">
            <i class="fas fa-music"></i> Lineup Song
        </a>
        <a href="member.php" class="btn btn-custom btn-members">
            <i class="fas fa-users"></i> View UCKGC Members
        </a>
        <a href="homepage.php" class="btn btn-custom btn-logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

    <!-- Floating Action Button for Tutorial -->
    <div class="fab-tutorial" data-bs-toggle="modal" data-bs-target="#tutorialModal">
        <i class="fas fa-info-circle"></i>
    </div>

    <!-- Modal for Tutorial -->
    <div class="modal fade" id="tutorialModal" tabindex="-1" aria-labelledby="tutorialModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tutorialModalLabel">Dashboard Tutorial</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Here is a step-by-step tutorial on how to use the dashboard:</p>
                    <ul>
                        <li><strong>Register Member:</strong>  Add member to the system, and generate their QR code for attendance tracking.</li>
                        <li><strong>Members Attendance (Sunday):</strong>Record QR code attendance for members who are registered for Sunday services.</li>
                        <li><strong>UCKGC Events:</strong> Create, view, and manage events for the church. All members can scan their individual QR codes for attendance.</li>
                        <li><strong>View Attendance Log:</strong> View a log of all member attendance records.</li>
                        <li><strong>Lineup Song:</strong> Manage the lineup of songs for services.</li>
                        <li><strong>View UCKGC Members:</strong> View a list of all registered members.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (necessary for modal functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
