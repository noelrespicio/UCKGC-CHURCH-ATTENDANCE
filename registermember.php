<?php
session_start();
require_once "database.php";
require_once "phpqrcode/qrlib.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = "";
$qrCodeGenerated = false;
$qrFile = "";
$qrData = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $address = trim($_POST["address"]);
    $category = trim($_POST["category"]);
    
    if (empty($first_name) || empty($last_name) || empty($address) || empty($category)) {
        $message = "<div class='alert alert-danger text-center'><i class='bi bi-x-circle-fill'></i> All fields are required.</div>";
    } else {
        // Check if the member already exists for the admin
        $stmt = $conn->prepare("SELECT id FROM attendees WHERE first_name = :first_name AND last_name = :last_name AND address = :address AND admin_id = :admin_id");
        $stmt->execute([':first_name' => $first_name, ':last_name' => $last_name, ':address' => $address, ':admin_id' => $admin_id]);

        if ($stmt->rowCount() > 0) {
            $message = "<div class='alert alert-warning text-center'><i class='bi bi-person-fill-exclamation'></i> UCKGC member already registered by you!</div>";
        } else {
            // Register new member and generate QR code
            $stmt = $conn->prepare("INSERT INTO attendees (first_name, last_name, address, category, admin_id) VALUES (:first_name, :last_name, :address, :category, :admin_id)");
            
            if ($stmt->execute([':first_name' => $first_name, ':last_name' => $last_name, ':address' => $address, ':category' => $category, ':admin_id' => $admin_id])) {
                // Create QR code for the registered member
                $qrData = strtoupper($first_name) . "_" . strtoupper($last_name);
                $qrFile = "qrcodes/" . $qrData . ".png";
                QRcode::png($qrData, $qrFile, QR_ECLEVEL_L, 6);

                // Update the record with the QR code file path
                $stmt = $conn->prepare("UPDATE attendees SET qr_code = :qr_code WHERE first_name = :first_name AND last_name = :last_name AND admin_id = :admin_id");
                $stmt->execute([':qr_code' => $qrFile, ':first_name' => $first_name, ':last_name' => $last_name, ':admin_id' => $admin_id]);

                $qrCodeGenerated = true;

                $message = "<div class='alert alert-success text-center'>
                                <i class='bi bi-check-circle-fill'></i>  
                                <strong>Success!</strong> UCKGC member registered successfully! Your QR code is ready.<br>
                                <a href='dashboard.php' class='btn btn-dark mt-3'><i class='bi bi-house-door-fill'></i> Back to Homepage</a>
                            </div>";
            } else {
                $message = "<div class='alert alert-danger text-center'><i class='bi bi-exclamation-octagon-fill'></i> Error registering UCKGC member!</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            padding: 20px;
        }
        .register-container {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
            text-align: center;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: 0.3s;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .form-control, .form-select {
            border-radius: 10px;
        }
        .btn-dark {
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="register-container">
    <h2 class="text-center mb-3"><i class="bi bi-person-plus-fill"></i> Register Member</h2>
    <?php echo $message; ?>
    <form action="registermember.php" method="POST">
        <div class="mb-3">
            <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
        </div>
        <div class="mb-3">
            <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
        </div>
        <div class="mb-3">
            <input type="text" class="form-control" name="address" placeholder="Address" required>
        </div>
        <div class="mb-3">
            <select class="form-select" name="category" required>
                <option value="">Select Category</option>
                <option value="Kid">Kid</option>
                <option value="Youth">Youth</option>
                <option value="Adult">Adult</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-qr-code"></i> Register & Generate QR</button>
    </form>
</div>

<?php if ($qrCodeGenerated): ?>
<div class="modal fade" id="qrModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-qr-code-scan"></i> QR Code Generated</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="<?php echo $qrFile; ?>" alt="QR Code" class="img-fluid rounded shadow-sm">
                <p class="mt-2"><strong>QR Code ID:</strong> <?php echo $qrData; ?></p>
                <a href="<?php echo $qrFile; ?>" download class="btn btn-success"><i class="bi bi-download"></i> Download QR Code</a>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var qrModal = new bootstrap.Modal(document.getElementById("qrModal"));
        qrModal.show();
    });
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
