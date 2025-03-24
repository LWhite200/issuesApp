<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

if ($user['admin'] != 1) {
    die("You do not have permission to edit this issue.");
}

if (!isset($_GET['id'])) {
    die("Issue ID is required.");
}

$issue_id = $_GET['id'];

// Fetch the issue details
$stmt = $conn->prepare("SELECT * FROM iss_issues WHERE id = ?");
$stmt->bind_param("i", $issue_id);
$stmt->execute();
$result = $stmt->get_result();
$issue = $result->fetch_assoc();

if (!$issue) {
    die("Issue not found.");
}

// Update issue details when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];
    $per_id = $_POST['per_id'];  // Person ID who is responsible for the issue

    // Update the issue information
    $updateStmt = $conn->prepare("UPDATE iss_issues SET short_description = ?, long_description = ?, priority = ?, org = ?, project = ?, per_id = ? WHERE id = ?");
    $updateStmt->bind_param("sssssii", $short_description, $long_description, $priority, $org, $project, $per_id, $issue_id);

    if ($updateStmt->execute()) {
        header("Location: issue.php?id=$issue_id&message=Issue updated successfully");
        exit();
    } else {
        die("Error updating issue: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Issue</title>
</head>
<body>
    <h1>Edit Issue</h1>

    <form method="POST" action="">
        <label for="short_description">Short Description:</label>
        <input type="text" name="short_description" id="short_description" value="<?php echo htmlspecialchars($issue['short_description']); ?>" required><br><br>

        <label for="long_description">Long Description:</label>
        <textarea name="long_description" id="long_description" required><?php echo htmlspecialchars($issue['long_description']); ?></textarea><br><br>

        <label for="priority">Priority:</label>
        <select name="priority" id="priority" required>
            <option value="Low" <?php echo $issue['priority'] == 'Low' ? 'selected' : ''; ?>>Low</option>
            <option value="Medium" <?php echo $issue['priority'] == 'Medium' ? 'selected' : ''; ?>>Medium</option>
            <option value="High" <?php echo $issue['priority'] == 'High' ? 'selected' : ''; ?>>High</option>
        </select><br><br>

        <label for="org">Organization:</label>
        <input type="text" name="org" id="org" value="<?php echo htmlspecialchars($issue['org']); ?>" required><br><br>

        <label for="project">Project:</label>
        <input type="text" name="project" id="project" value="<?php echo htmlspecialchars($issue['project']); ?>" required><br><br>

        <label for="per_id">Assigned Person:</label>
        <input type="number" name="per_id" id="per_id" value="<?php echo $issue['per_id']; ?>" required><br><br>

        <button type="submit">Update Issue</button>
    </form>

    <p><a href="issue.php?id=<?php echo $issue['id']; ?>">Back to Issue</a></p>
</body>
</html>

<?php $conn->close(); ?>
