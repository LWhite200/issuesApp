<?php
include 'config.php';  // Include database configuration

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Generate password hash and salt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Extract the salt from the hash
    // The salt is the first 29 characters of the hash
    $salt = substr($hashed_password, 0, 29);

    // Prepare the SQL statement to insert the new user into the database
    $stmt = $conn->prepare("INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, pwd_salt) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fname, $lname, $mobile, $email, $hashed_password, $salt);

    // Execute the query and check for success
    if ($stmt->execute()) {
        // Registration successful - redirect to login page (or directly to the issue list if desired)
        header("Location: login.php");
        exit();
    } else {
        // Error in registration
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Registration</title>
    <meta charset="utf-8">
</head>
<body>
    <h2>Register</h2>
    <form method="post" action="registration.php">
        <label>First Name:</label>
        <input type="text" name="fname" required><br>

        <label>Last Name:</label>
        <input type="text" name="lname" required><br>

        <label>Mobile:</label>
        <input type="text" name="mobile" required><br>

        <label>Email:</label>
        <input type="email" name="email" required><br>

        <label>Password:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>
