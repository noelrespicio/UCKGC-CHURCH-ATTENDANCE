<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page | Medicine Care Inventory Manager</title>
    <link rel="icon" href="logo/icons8-medicine-cabinet-32.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background: url('cover/walpaper.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            color: #fff;
        }
        
        .homepage-container {
            display: flex;
            align-items: center;
            gap: 20px;
            background: rgba(255, 255, 255, 0.0);
            padding: 30px;
            border-radius: 15px;
            max-width: 900px;
            width: 90%;
            margin-top: 0px; /* Moves the container to the top */
        }

        .homepage-container img {
            width: 140px;
            height: auto;
        }

        .text-content {
            text-align: left;
        }

        .text-content h1 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #fff;
        }

        .text-content p {
            font-size: 1.1rem;
            color: #e6e6e6;
            margin-bottom: 5px;
        }

        .login-button {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .login-button .btn {
            background-color: rgba(0, 121, 107, 0.8);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: #fff;
            font-size: 1rem;
            transition: background-color 0.3s, transform 0.2s;
        }

        .login-button .btn:hover {
            background-color: rgba(0, 77, 64, 0.8);
            transform: scale(1.05);
        }

        .footer {
            margin-top: auto;
            text-align: center;
            padding: 10px;
            background-color: rgba(0, 121, 107, 0.8);
            color: white;
            width: 100%;
            position: fixed;
            bottom: 0;
        }
    </style>
</head>
<body>
    <div class="login-button">
        <a href="login.php" class="btn">Login</a>
    </div>

    <div class="homepage-container">
        <img src="cover/481151198_1440757323568385_4474186236176026997_n-removebg-preview.png" alt="Church Logo">
        <div class="text-content">
            <h2 id="greeting"></h2>
            <h1>UNITED COMMUNITY KINGDOM OF GOD CHURCH</h1>
            <p><i>Luke 17:21</i></p>
        </div>
    </div>

    <div class="footer">
        &copy; 2025 UCKGC Phil. All Rights Reserved.
    </div>

    <script>
        const greetingElement = document.getElementById('greeting');
        const currentHour = new Date().getHours();

        if (currentHour < 12) {
            greetingElement.textContent = 'Welcome!';
        } else if (currentHour < 18) {
            greetingElement.textContent = 'Welcome!';
        } else {
            greetingElement.textContent = 'Welcome!';
        }
    </script>
</body>
</html>
