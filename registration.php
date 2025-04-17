<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $salt = substr($hashed_password, 0, 29);

    $stmt = $conn->prepare("INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, pwd_salt) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fname, $lname, $mobile, $email, $hashed_password, $salt);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        $error_message = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Registration</title>
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

        .register-container {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 460px;
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
        input[type="email"],
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

        input:focus {
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
    <div class="register-container">
        <h2>Register</h2>
        <form method="post" action="registration.php">
            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" required>

            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" required>

            <label for="mobile">Mobile:</label>
            <input type="text" id="mobile" name="mobile" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Register</button>
        </form>

        <?php if (isset($error_message)) { echo "<p class='error'>$error_message</p>"; } ?>

        <p><a href="login.php">Back to login</a></p>
    </div>
</body>
</html>
