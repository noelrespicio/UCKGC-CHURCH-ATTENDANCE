<?php
require_once "database.php";
session_start();

// Siguraduhin na Main Admin lang ang may access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'superadmin') {
    die("Access denied.");
}

// Kunin ang data ng admins at bilang ng members bawat isa
$stmt = $conn->prepare("
    SELECT a.location, a.email, COUNT(m.id) AS member_count 
    FROM admins a
    LEFT JOIN attendees m ON a.id = m.admin_id
    WHERE a.role = 'admin'
    GROUP BY a.email
");
$stmt->execute();
$adminData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
        }
        .dashboard-container {
            max-width: 900px;
            margin: auto;
            padding-top: 7px;
        }

        .dashboard-container h2 {
    font-size: 32px; /* Corrected from 'size' to 'font-size' */
}

        .card-custom {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .btn-custom {
            border-radius: 50px;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
        .chart-container {
            padding: 20px;
            min-height: 350px;
        }
    </style>
</head>
<body>

    <div class="container dashboard-container">
        <!-- Top Bar with Logout Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary">UNITED COMMUNITY KINGDOM OF GOD CHURCH</h2>
            <a href="homepage.php" class="btn btn-danger btn-lg shadow-sm fw-bold">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>

        <div class="text-center mb-4">
            <a href="superadmin.php" class="btn btn-primary btn-custom">👥 View All Church's Admin</a>
        </div>

        <div class="card card-custom">
            <div class="card-header bg-dark text-white text-center fw-bold">
                📊 Number of Members per Church
            </div>
            <div class="card-body chart-container">
                <canvas id="adminChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        var ctx = document.getElementById('adminChart').getContext('2d');
        var adminChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($adminData, 'location')) ?>,
                datasets: [{
                    label: 'Number of Members',
                    data: <?= json_encode(array_column($adminData, 'member_count')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                animation: {
                    duration: 1500,
                    easing: 'easeInOutBounce'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
