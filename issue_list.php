<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

// Fetch all issues
$issues = $conn->query("SELECT * FROM iss_issues")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List</title>
</head>
<body>
    <h1>All Issues</h1>

    <!-- Allow all authenticated users to add an issue -->
    <p><a href="add_issue.php">Add New Issue</a></p>

    <?php if (count($issues) > 0): ?>
        <ul>
            <?php foreach ($issues as $issue): ?>
                <li>
                    <a href="issue.php?id=<?php echo $issue['id']; ?>"><?php echo htmlspecialchars($issue['short_description']); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No issues found. <a href="add_issue.php">Add a new issue</a></p>
    <?php endif; ?>
</body>
</html>

<?php $conn->close(); ?>
