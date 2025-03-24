<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;
$person_id = $user['id']; // Person ID is the currently logged-in user's ID

// Handle Add or Update Issue (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $open_date = $_POST['open_date'];
    // Remove closed date from the form data since it's no longer being used
    $priority = $_POST['priority']; // Simple string for priority
    $org = $_POST['org'];
    $project = $_POST['project'];

    if (isset($_POST['update'])) {
        // Update existing issue
        $id = $_POST['id'];
        $query = "UPDATE iss_issues SET short_description=?, long_description=?, open_date=?, priority=?, org=?, project=?, per_id=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssisi", $short_description, $long_description, $open_date, $priority, $org, $project, $person_id, $id);
        $success = $stmt->execute();
    } else {
        // Add new issue
        $query = "INSERT INTO iss_issues (short_description, long_description, open_date, priority, org, project, per_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $short_description, $long_description, $open_date, $priority, $org, $project, $person_id);
        $success = $stmt->execute();
    }

    $_SESSION['message'] = $success ? "Action successful!" : "Error: " . $conn->error;
    header("Location: issue_list.php");
    exit();
}

// Fetch the issue to update (GET)
$edit_issue = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM iss_issues WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_issue = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($edit_issue) ? "Edit Issue" : "Add Issue"; ?></title>
</head>
<body>
    <h1><?php echo isset($edit_issue) ? "Edit Issue" : "Add Issue"; ?></h1>

    <form action="" method="POST">
        <input type="hidden" name="id" value="<?php echo $edit_issue['id'] ?? ''; ?>">

        <label>Short Description: <input type="text" name="short_description" value="<?php echo $edit_issue['short_description'] ?? ''; ?>" required></label><br>
        <label>Long Description: <textarea name="long_description" required><?php echo $edit_issue['long_description'] ?? ''; ?></textarea></label><br>
        <label>Open Date: <input type="date" name="open_date" value="<?php echo $edit_issue['open_date'] ?? ''; ?>" required></label><br>
        <label>Priority: <input type="text" name="priority" value="<?php echo $edit_issue['priority'] ?? ''; ?>" required></label><br>
        <label>Organization: <input type="text" name="org" value="<?php echo $edit_issue['org'] ?? ''; ?>" required></label><br>
        <label>Project: <input type="text" name="project" value="<?php echo $edit_issue['project'] ?? ''; ?>" required></label><br>

        <button type="submit" name="<?php echo isset($edit_issue) ? 'update' : 'add'; ?>">
            <?php echo isset($edit_issue) ? 'Update Issue' : 'Add Issue'; ?>
        </button>
    </form>

    <p><a href="issue_list.php">Back to Issue List</a></p>
</body>
</html>

<?php $conn->close(); ?>
