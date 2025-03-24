<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

if (isset($_GET['id'])) {
    $person_id = $_GET['id'];

    // Fetch person details
    $stmt = $conn->prepare("SELECT * FROM iss_persons WHERE id = ?");
    $stmt->bind_param("i", $person_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $person = $result->fetch_assoc();

    if (!$person) {
        die("Person not found.");
    }
} else {
    die("Person ID is required.");
}

// Handle deletion
if (isset($_GET['delete']) && $_GET['delete'] == $person_id) {
    // Prevent the logged-in user from deleting themselves
    if ($user && $user['id'] == $person_id) {
        // Redirect to login page if the logged-in user is trying to delete themselves
        header("Location: login.php");
        exit();
    }

    // Admin can delete any person
    if ($user && $user['admin'] == 1) {
        $deleteStmt = $conn->prepare("DELETE FROM iss_persons WHERE id = ?");
        $deleteStmt->bind_param("i", $person_id);

        if ($deleteStmt->execute()) {
            // Redirect to issue list after successful deletion
            header("Location: issue_list.php?message=Person deleted successfully");
            exit();
        } else {
            die("Error deleting person: " . $conn->error);
        }
    } else {
        die("You do not have permission to delete this person.");
    }
}

// Handle editing
if (isset($_GET['edit']) && $_GET['edit'] == $person_id) {
    if ($user && ($user['admin'] == 1 || $user['id'] == $person_id)) {
        // Admin can edit anyone, and the person can edit themselves
        header("Location: edit_person.php?id=" . $person_id);
        exit();
    } elseif ($user && $user['id'] == $person_id) {
        // Prevent the logged-in user from editing themselves (redirect to login)
        header("Location: login.php");
        exit();
    } else {
        die("You do not have permission to edit this person.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Person Details</title>
</head>
<body>
    <h1>Person Details</h1>

    <p><strong>ID:</strong> <?php echo $person['id']; ?></p>
    <p><strong>First Name:</strong> <?php echo htmlspecialchars($person['fname']); ?></p>
    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($person['lname']); ?></p>
    <p><strong>Mobile:</strong> <?php echo htmlspecialchars($person['mobile']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($person['email']); ?></p>
    
    <?php if ($user && ($user['admin'] == 1 || $user['id'] == $person['id'])): ?>
        <!-- Admin and the person themselves can edit and delete -->
        <p><a href="edit_person.php?id=<?php echo $person['id']; ?>">Edit Person</a></p>
        <p><a href="person.php?id=<?php echo $person['id']; ?>&delete=<?php echo $person['id']; ?>">Delete Person</a></p>
    <?php endif; ?>

    <p><a href="issue_list.php">Back to Issue List</a></p>
</body>
</html>

<?php $conn->close(); ?>
