<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

if (!isset($_GET['id'])) {
    die("Person ID is required.");
}

$person_id = $_GET['id'];

// Check if the user is an admin
if ($user['admin'] != 1) {
    die("You do not have permission to edit this person.");
}

// Fetch the person's details
$stmt = $conn->prepare("SELECT * FROM iss_persons WHERE id = ?");
$stmt->bind_param("i", $person_id);
$stmt->execute();
$result = $stmt->get_result();
$person = $result->fetch_assoc();

if (!$person) {
    die("Person not found.");
}

// Update person details when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    $admin = isset($_POST['admin']) ? 1 : 0;  // Admin status

    // Update the person's information
    $updateStmt = $conn->prepare("UPDATE iss_persons SET fname = ?, lname = ?, mobile = ?, email = ?, admin = ? WHERE id = ?");
    $updateStmt->bind_param("ssssii", $fname, $lname, $mobile, $email, $admin, $person_id);

    if ($updateStmt->execute()) {
        header("Location: person.php?id=$person_id&message=Person updated successfully");
        exit();
    } else {
        die("Error updating person: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Person</title>
</head>
<body>
    <h1>Edit Person</h1>

    <form method="POST" action="">
        <label for="fname">First Name:</label>
        <input type="text" name="fname" id="fname" value="<?php echo htmlspecialchars($person['fname']); ?>" required><br><br>

        <label for="lname">Last Name:</label>
        <input type="text" name="lname" id="lname" value="<?php echo htmlspecialchars($person['lname']); ?>" required><br><br>

        <label for="mobile">Mobile:</label>
        <input type="text" name="mobile" id="mobile" value="<?php echo htmlspecialchars($person['mobile']); ?>" required><br><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($person['email']); ?>" required><br><br>

        <label for="admin">Is Admin:</label>
        <input type="checkbox" name="admin" id="admin" <?php echo $person['admin'] == 1 ? 'checked' : ''; ?>><br><br>

        <button type="submit">Update Person</button>
    </form>

    <p><a href="person.php?id=<?php echo $person['id']; ?>">Back to Person</a></p>
</body>
</html>

<?php $conn->close(); ?>
