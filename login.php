
<!-- 
Login from a previous assignment 
css made by chtgpt, it's funny making it fancy when no other page is
-->

<?php
include 'config.php';
session_start(); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['pass'];

    $stmt = $conn->prepare("SELECT * FROM iss_persons WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['pwd_hash'])) {
            $_SESSION['user'] = $user;
            header("Location: issue_list.php");
            exit();
        } else {
            $error_message = "Invalid email or password.";
        }
    } else {
        $error_message = "No user found with this email.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Trebuchet+MS&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Trebuchet MS', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(to bottom, #c9e4f7, #aee0f5 50%, #e8f9ff 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-attachment: fixed;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 420px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        h2 {
            text-align: center;
            margin-bottom: 24px;
            color: #004c77;
            text-shadow: 1px 1px 1px rgba(255,255,255,0.8);
        }

        label {
            display: block;
            margin-top: 15px;
            color: #003d5c;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border: 1px solid #99d1ea;
            border-radius: 8px;
            background-color: #f0fbff;
            box-shadow: inset 1px 1px 3px rgba(0, 0, 0, 0.05);
            transition: border 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border: 1px solid #3ca9dd;
            outline: none;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background: linear-gradient(to bottom, #50b5e0, #2898c1);
            border: none;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background: linear-gradient(to bottom, #2898c1, #50b5e0);
            transform: translateY(-2px);
        }

        .error {
            margin-top: 15px;
            color: #b00020;
            background: rgba(255, 255, 255, 0.6);
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        p {
            margin-top: 20px;
            text-align: center;
        }

        a {
            color: #004c77;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <form method="post" action="login.php">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>
            
            <label for="pass">Password:</label>
            <input type="password" id="pass" name="pass" required>
            
            <button type="submit">Login</button>
        </form>

        <?php if (isset($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>

        <p><a href="registration.php">Register here</a></p>
    </div>
</body>
</html>
