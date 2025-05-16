<?php
require('fpdf/fpdf.php');

// Ensure the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid access.");
}

// Handle file uploads
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Save Church Logo (Required)
if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
    $logoPath = $uploadDir . basename($_FILES['logo']['name']);
    move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath);
} else {
    die("Church logo is required.");
}

// Save Signature (Optional)
$signaturePath = "";
if (isset($_FILES['signature']) && $_FILES['signature']['error'] == 0) {
    $signaturePath = $uploadDir . basename($_FILES['signature']['name']);
    move_uploaded_file($_FILES['signature']['tmp_name'], $signaturePath);
}

// Get form data
$name = htmlspecialchars($_POST['name']);
$month = htmlspecialchars($_POST['month']);
$pastorName = htmlspecialchars($_POST['pastor_name']);
$pastorTitle = htmlspecialchars($_POST['pastor_title']);

// Create PDF
$pdf = new FPDF('L', 'mm', 'Letter'); // Landscape, millimeters, Short Bond Paper (8.5 x 11 inches)
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Background Color
$pdf->SetFillColor(255, 250, 250);
$pdf->Rect(0, 0, 280, 216, 'F');

// Gold Border
$pdf->SetLineWidth(5);
$pdf->SetDrawColor(218, 165, 32);
$pdf->Rect(10, 10, 255, 190);

// Church Logo
$pdf->Image($logoPath, 115, 15, 42);

// Title "CERTIFICATE OF APPRECIATION"
$pdf->SetFont('Arial', 'B', 32);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 105, "CERTIFICATE OF APPRECIATION", 0, 1, 'C');
$pdf->Ln(-40);

// "This certifies that"
$pdf->SetFont('Arial', '', 22);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, "This certifies that", 0, 1, 'C');
$pdf->Ln(5);

// Recipient's Name (Bold & Larger)
$pdf->SetFont('Arial', 'B', 38);
$pdf->SetTextColor(204, 0, 0);
$pdf->Cell(0, 20, strtoupper($name), 0, 1, 'C');
$pdf->Ln(5);

// Appreciation Message
$pdf->SetFont('Arial', '', 20);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 12, "is sincerely appreciated for faithful participation", 0, 1, 'C');
$pdf->Cell(0, 12, "in all Sundays for the month of " . strtoupper($month), 0, 1, 'C');
$pdf->Ln(20);

// Get current Y position before inserting the signature
$yBeforeImage = $pdf->GetY();

// Add Signature Image (if provided)
if ($signaturePath) {
    $pdf->Image($signaturePath, 118, $yBeforeImage - 20, 40, 15);
}

// Adjust spacing to ensure the name doesn't overlap
$pdf->Ln(-7);

$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 3, $pastorName, 0, 1, 'C'); // Name on top
$pdf->Cell(0, 0, "_________________", 0, 1, 'C'); // Line (no extra space)
$pdf->SetFont('Arial', 'I', 16);
$pdf->Cell(0, 12, $pastorTitle, 0, 1, 'C'); // Position directly below the line
$pdf->Ln(2);

// Date Issued
$pdf->SetFont('Arial', 'I', 16);
$pdf->SetTextColor(0, 51, 102);
$pdf->Cell(0, 10, "Issued on: " . date("F d, Y"), 0, 1, 'C');
$pdf->Ln(5);

// Bible Verse
$pdf->SetFont('Arial', 'I', 14);
$pdf->SetTextColor(139, 69, 19);
$pdf->MultiCell(0, 8, "\"Whatever you do, work at it with all your heart, as working for the Lord, not for human masters.\" - Colossians 3:23", 0, 'C');

// Output PDF
$pdf->Output("D", "Certificate_of_Appreciation_$name.pdf");
?>
