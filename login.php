<?php
include 'config.php';
session_start(); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['pass'];

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM persons WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a user with the provided email exists
    if ($user = $result->fetch_assoc()) {
        // User exists, now verify password hash
        if (password_verify($password, $user['pwd_hash'])) {
            $_SESSION['user'] = $user;
            header("Location: crud.php");
            exit();
        } else {
            $error_message = "Invalid email or password.";
        }
    } else {
        // No user found with that email
        $error_message = "sthfthg.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta charset="utf-8">
</head>
<body>  
    <h2>Login</h2>
    <form method="post" action="login.php">
        <label>Email:</label>
        <input type="text" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="pass" required><br>
        <button type="submit">Login</button>
    </form>
    
    <?php if (isset($error_message)) { echo "<p style='color:red;'>$error_message</p>"; } ?>
    
    <p><a href="registration.php">Register here</a></p>
    <p><a href="home-page.php">Return to home</a></p>
</body>
</html>
