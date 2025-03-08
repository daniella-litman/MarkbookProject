<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Markbook Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=EB+Garamond&family=Playfair+Display:wght@400;600&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', 'EB Garamond', serif;
        }

        body, html {
            height: 100%;
            background-color: #f5f0e6;
        }

        .container {
            display: flex;
            width: 100%;
            height: 100vh;
        }

        .left {
            width: 50%;
            background: url('https://i.pinimg.com/736x/26/1a/02/261a02557aac4051ffcd091fee0af13a.jpg');
            background-size: cover;
            filter: brightness(80%) contrast(90%);
        }

        .right {
            width: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #ede0d4;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            text-align: center;
            border: 1px solid #b08968;
        }

        .login-box h1 {
            margin-bottom: 20px;
            font-size: 30px;
            font-weight: bold;
            color: #5e503f;
            font-family: 'Playfair Display', serif;
        }

        .login-box label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            text-align: left;
            color: #4a3b30; 
        }

        .login-box input, .login-box select {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border: 1px solid #a68a7f;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            background: #fef6e4;
            color: #3e2c23;
        }

        .login-box input::placeholder {
            color: #a89988;
            font-style: italic;
        }

        .login-box input[type="submit"] {
            width: 100%;
            background-color: #7f5539;
            color: white;
            padding: 12px;
            margin-top: 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 6px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .login-box input[type="submit"]:hover {
            background-color: #5e4031;
            transform: scale(1.02);
        }

        .login-box select {
            appearance: none;
            background: #fef6e4;
            background-size: 12px;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left, .right {
                width: 100%;
                height: 50vh;
            }

            .login-box {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left"></div>

    <div class="right">
        <div class="login-box">
            <h1>Markbook Login</h1>
            <form action="markbookLoginSubmit.php" method="GET">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Enter your username" required>

                <label for="pass">Password</label>
                <input type="password" name="pass" id="pass" placeholder="Enter your password" required>

                <label for="role">Role</label>
                <select name="role" id="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="teacher">Teacher</option>
                    <option value="student">Student</option>
                </select>

                <input type="submit" value="Log in">
            </form>
        </div>
    </div>
</div>

</body>
</html>
