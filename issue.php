<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch issue details
    $stmt = $conn->prepare("SELECT * FROM iss_issues WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $issue = $result->fetch_assoc();

    if (!$issue) {
        die("Issue not found.");
    }

    // Fetch person details based on person ID
    $person_id = $issue['per_id'];
    $personStmt = $conn->prepare("SELECT fname, lname FROM iss_persons WHERE id = ?");
    $personStmt->bind_param("i", $person_id);
    $personStmt->execute();
    $personResult = $personStmt->get_result();
    $person = $personResult->fetch_assoc();

} else {
    die("Issue ID is required.");
}

// Handle the deletion
if (isset($_GET['delete']) && $_GET['delete'] == $id) {
    if (($user && $user['admin'] == 1) || ($user && $user['id'] == $issue['per_id'])) {
        // Perform deletion
        $deleteStmt = $conn->prepare("DELETE FROM iss_issues WHERE id = ?");
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {
            // Redirect to issue list after successful deletion, no query parameters
            header("Location: issue_list.php?message=Issue deleted successfully");
            exit();
        } else {
            die("Error deleting issue: " . $conn->error);
        }
    } else {
        die("You do not have permission to delete this issue.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($issue['short_description']); ?> Details</title>
</head>
<body>
    <h1>Issue Details</h1>

    <p><strong>ID:</strong> <?php echo $issue['id']; ?></p>
    <p><strong>Short Description:</strong> <?php echo htmlspecialchars($issue['short_description']); ?></p>
    <p><strong>Long Description:</strong> <?php echo htmlspecialchars($issue['long_description']); ?></p>
    <p><strong>Open Date:</strong> <?php echo htmlspecialchars($issue['open_date']); ?></p>
    <p><strong>Closed Date:</strong> <?php echo htmlspecialchars($issue['closed_date'] ?? 'Open'); ?></p>
    <p><strong>Priority:</strong> <?php echo htmlspecialchars($issue['priority']); ?></p>
    <p><strong>Organization:</strong> <?php echo htmlspecialchars($issue['org']); ?></p>
    <p><strong>Project:</strong> <?php echo htmlspecialchars($issue['project']); ?></p>
    
    <?php if ($person): ?>
        <p><strong>Person:</strong> <a href="person.php?id=<?php echo $person_id; ?>"><?php echo htmlspecialchars($person['fname']) . ' ' . htmlspecialchars($person['lname']); ?></a></p>
    <?php else: ?>
        <p><strong>Person:</strong> Person deleted</p>
    <?php endif; ?>

    <?php if (($user && $user['admin'] == 1) || ($user && $user['id'] == $issue['per_id'])): ?>
        <p><a href="edit_issue.php?id=<?php echo $issue['id']; ?>">Edit Issue</a></p>

        <p><a href="issue.php?id=<?php echo $issue['id']; ?>&delete=<?php echo $issue['id']; ?>">Delete Issue</a></p>
    <?php endif; ?>

    <p><a href="issue_list.php">Back to Issue List</a></p>
</body>
</html>

<?php $conn->close(); ?>
