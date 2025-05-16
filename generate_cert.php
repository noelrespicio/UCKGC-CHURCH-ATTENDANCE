<?php
if (isset($_GET['name']) && isset($_GET['month'])) {
    $recipient_name = htmlspecialchars($_GET['name']);
    $month = htmlspecialchars($_GET['month']);
} else {
    die("Invalid request.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Certificate</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: 'Georgia', serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
        }
        .container {
            display: flex;
            width: 90%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .form-container {
            width: 40%;
            padding: 20px;
        }
        .preview-container {
            width: 60%;
            text-align: center;
            padding: 70px;
            background: #f8f9fa;
            border-radius: 0 10px 10px 0;
            border-left: 2px solid #ddd;
        }
        input, select, button {
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .certificate {
            width: 100%;
            aspect-ratio: 11/8.5;
            padding: 30px;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            position: relative;
            text-align: center;
            border: 10px solid #333;
        }
        .template1 { border: 10px solid #333; }
        .template2 { border: 10px double #0056b3; }
        .template3 { border: 10px dashed #28a745; }
        .template4 { border: 10px groove #dc3545; }
        .template5 { border: 10px solid gold; }
        .template6 { border: 10px double purple; }
        .template7 { border: 10px dashed navy; }
        .template8 { border: 10px groove maroon; }
        .template9 { border: 10px solid teal; }
        .template10 { border: 10px double darkorange; }
        .template11 { border: 12px ridge #6f42c1; background: linear-gradient(to right, #fdfbfb, #ebedee); }
.template12 { border: 12px dotted #ff5733; background: linear-gradient(to bottom, #ffebcd, #ffdab9); }
.template13 { border: 14px double #007f5f; background: radial-gradient(circle, #ffffff, #d4edda); }
.template14 { border: 12px solid #1b1b1b; background: repeating-linear-gradient(45deg, #fff, #f8f9fa 10px, #e9ecef 10px, #dee2e6 20px); }
.template15 { border: 12px groove #ffcc00; background: linear-gradient(135deg, #fff3cd, #ffdd57); }
.template16 { border: 12px outset #0dcaf0; background: linear-gradient(to right, #e3f2fd, #90caf9); }
.template17 { border: 14px dotted #d63384; background: linear-gradient(to bottom, #f8f9fa, #ffc0cb); }
.template20 { border: 12px groove #fd7e14; background: radial-gradient(circle, #fff3cd, #ffdab9); }
.template21 { border: 10px dashed #6610f2; background: linear-gradient(to right, #f8f9fa, #d1c4e9); }
.template22 { border: 14px solid #20c997; background: radial-gradient(circle, #e3fdfd, #c8e6c9); }
.template23 { border: 12px double #dc3545; background: linear-gradient(to bottom, #ffffff, #ffebee); }


        .cert-title { font-size: 28px; font-weight: bold; text-transform: uppercase; margin-top: 90px; }
        .cert-name { font-size: 30px; font-weight: bold; color: #d9534f; }
        .cert-text { font-size: 18px; }
        .signature {
            margin-top: 70px; 
        }
        .signature p {
            
            margin: 0;
        }
        .signature .line {
            border-top: 2px solid #000;
            display: block;
            width: 200px;
            margin: 5px auto;
        }
        .cert-verse { font-size: 16px; font-style: italic; }
        .logo {
            position: absolute;
            top: 10px;
            left: 260px;
            width: 100px;
            height: 100px;
        }#cert-signature {
    position: absolute;
            top: 360px;
            left: 230px;
            width: 150px;
            height: 50px;
}

    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Certificate Generator</h2>
            <form>
            <label for="logo-upload">Upload Logo:</label>
            <input type="file" id="logo-upload" accept="image/*" onchange="previewLogo()">
                <label for="template">Select Template:</label>
                <select id="template" onchange="changeTemplate()">
                    <option value="template1">Classic</option>
                    <option value="template2">Elegant Blue</option>
                    <option value="template3">Green Dashed</option>
                    <option value="template4">Red Groove</option>
                    <option value="template5">Golden Frame</option>
                    <option value="template6">Royal Purple</option>
                    <option value="template7">Navy Dashed</option>
                    <option value="template8">Maroon Groove</option>
                    <option value="template9">Teal Solid</option>
                    <option value="template10">Teal Solid</option>
                    <option value="template12">1</option>
                    <option value="template13">2</option>
                    <option value="template14">3</option>
                    <option value="template15">4</option>
                    <option value="template16">6</option>
                    <option value="template17">7</option>
                    <option value="template20">8</option>
                    <option value="template21">9</option>
                    <option value="template23">10</option>
                </select>
              
                </select>
                <label for="pastor_name">Pastor Name:</label>
                <input type="text" id="pastor_name" oninput="updateCertificate()" required>
                <label for="pastor_title">Title:</label>
                <input type="text" id="pastor_title" oninput="updateCertificate()" required>
                <label for="pastor_signature">Upload Pastor's Signature (Optional):</label>
                <input type="file" id="pastor_signature" accept="image/*" onchange="updateSignature()">
                <label for="verse">Bible Verse:</label>
                <input type="text" id="verse" oninput="updateCertificate()" required>
            </form>
            <button onclick="downloadCertificate()">Download Certificate</button>
        </div>
        <div class="preview-container">
            <div class="certificate template1" id="certificate-preview">
            <img id="cert-logo" class="logo" src="" style="display: none;">
                <div class="cert-title" id="cert-title">Certificate of Appreciation</div>
                <p class="cert-text">This certifies that</p>
                <p class="cert-name" id="cert-name"><strong><?php echo $recipient_name; ?></strong></p>
                <p class="cert-text">is sincerely appreciated for faithful participation<br>in all Sundays for the month of <strong><?php echo $month; ?></strong>.</p></p>
               
                <div class="signature">
                <img id="cert-signature" src="" style="display: none; max-width: 150px;">
                    <p id="cert-pastor-name">[Pastor Name]</p>
                    <p class="line"></p>
                    <p id="cert-pastor-title">[Title]</p>
                </div>
                <p class="cert-verse" id="cert-verse"></p>
            </div>
        </div>
    </div>
    <script>
        function updateSignature() {
    const fileInput = document.getElementById('pastor_signature');
    const certSignature = document.getElementById('cert-signature');
    
    if (fileInput.files && fileInput.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            certSignature.src = e.target.result;
            certSignature.style.display = 'block';
        };
        reader.readAsDataURL(fileInput.files[0]);
    } else {
        certSignature.style.display = 'none';
    }
}
        function updateCertificate() {
            document.getElementById("cert-pastor-name").textContent = document.getElementById("pastor_name").value || "[Pastor Name]";
            document.getElementById("cert-pastor-title").textContent = document.getElementById("pastor_title").value || "[Title]";
            document.getElementById("cert-verse").textContent = document.getElementById("verse").value || "";
        }
        function changeTemplate() {
            document.getElementById("certificate-preview").className = `certificate ${document.getElementById("template").value}`;
        }

        function downloadCertificate() {
            const { jsPDF } = window.jspdf;
            html2canvas(document.getElementById("certificate-preview"), { scale: 2 }).then(canvas => {
                const pdf = new jsPDF({
                    orientation: 'landscape',
                    unit: 'in',
                    format: 'letter'
                });
                const imgData = canvas.toDataURL('image/png');
                pdf.addImage(imgData, 'PNG', 0.5, 0.5, 10, 7.5);
                pdf.save("Certificate.pdf");
            });
        }
        function previewLogo() {
            const file = document.getElementById("logo-upload").files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("cert-logo").src = e.target.result;
                    document.getElementById("cert-logo").style.display = "block";
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
    </script>
</body>
</html>