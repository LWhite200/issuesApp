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
    <p><a href="logout.php">Logout Exit</a></p>
    <h1>All Issues</h1>

    <!-- Allow all authenticated users to add an issue -->
    <p><a href="add_issue.php">Add New Issue</a></p>

    <?php if (count($issues) > 0): ?>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Description</th>
                    <th>Open Date</th>
                    <th>Close Date</th>
                    <th>Priority</th>
                    <th>PDF Attached</th> <!-- New column -->
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues as $issue): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($issue['id']); ?></td>
                        <td><a href="issue.php?id=<?php echo $issue['id']; ?>"><?php echo htmlspecialchars($issue['short_description']); ?></a></td>
                        <td><?php echo htmlspecialchars($issue['open_date']); ?></td>
                        <td><?php echo htmlspecialchars($issue['close_date']); ?></td>
                        <td><?php echo htmlspecialchars($issue['priority']); ?></td>
                        
                        <!-- New column for checking if PDF is attached -->
                        <td>
                            <?php if (!empty($issue['pdf_attachment'])): ?>
                                <span>Yes</span> <!-- Indicating that a PDF is attached -->
                            <?php else: ?>
                                <span>N/A</span> <!-- Indicating no PDF is attached -->
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <!-- Action Buttons -->
                            <a href="issue.php?id=<?php echo $issue['id']; ?>">Read</a> | 
                            
                            <?php 
                            // Only show the Edit and Delete links if the user is the one who uploaded the issue or if the user is an admin
                            if ($user['id'] == $issue['per_id'] || $user['admin'] == 1): ?>
                                <a href="edit_issue.php?id=<?php echo $issue['id']; ?>">Edit</a> | 
                                <a href="delete_issue.php?id=<?php echo $issue['id']; ?>" onclick="return confirm('Are you sure you want to delete this issue?')">Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No issues found. <a href="add_issue.php">Add a new issue</a></p>
    <?php endif; ?>
</body>
</html>

<?php $conn->close(); ?>
