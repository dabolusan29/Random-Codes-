<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Portal Selection</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e0e6f8 0%, #ffffff 100%);
            margin: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        header {
            width: 100%;
            padding: 20px 0;
            background: linear-gradient(90deg, #0a2e59, #3a5ba0);
            color: #e0e6f8;
            font-weight: 700;
            font-size: 2.5rem;
            letter-spacing: 3px;
            text-align: center;
            text-shadow: 1px 1px 6px rgba(10, 46, 89, 0.7);
            box-shadow: 0 6px 15px rgba(10, 46, 89, 0.4);
            border-radius: 0 0 12px 12px;
            margin-bottom: 60px;
        }

        .selection-box {
            background: #f8faff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 6px 15px rgba(10, 46, 89, 0.15);
            text-align: center;
            width: 320px;
            animation: fadeIn 0.6s ease-in-out;
        }

        h1 {
            margin-bottom: 30px;
            color: #0a2e59;
            font-weight: 600;
            font-size: 2rem;
            text-shadow: 1px 1px 3px rgba(10, 46, 89, 0.3);
        }

        a {
            display: block;
            padding: 15px;
            margin: 15px 0;
            background: #0a2e59;
            color: #e0e6f8;
            text-decoration: none;
            font-weight: 600;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            box-shadow: 0 3px 7px rgba(10, 46, 89, 0.3);
            font-size: 1rem;
        }

        a:hover {
            background: #163f85;
            box-shadow: 0 5px 15px #0a2e59cc;
        }

        .back-link {
            background: #6c757d !important;
        }

        .back-link:hover {
            background: #565e64 !important;
            box-shadow: none !important;
        }

        @media (max-width: 400px) {
            .selection-box {
                padding: 25px 20px;
                width: 90%;
            }
            header {
                font-size: 2rem;
                letter-spacing: 2px;
                margin-bottom: 40px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<header>Hotel Management and Booking System</header>

<div class="selection-box">
    <h1>👨🏻‍💼 Admin Portal</h1>
    <a href="admin_login.php">🔐 Login as Admin</a>
    <a href="admin_signup.php">🔓 Sign Up as Admin</a>
    <a href="index.php" class="back-link">⬅️ Back to Main Selection</a>
</div>

</body>
</html>
