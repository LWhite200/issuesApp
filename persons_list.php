<!-- Will edit and delete in person.php, not here, this is last minute addition -->

<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

// Fetch all people
$persons = $conn->query("SELECT * FROM iss_persons ORDER BY lname ASC, fname ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Person List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="mb-4">All People</h1>

    <a href="issue_list.php" class="btn btn-secondary mb-3">Back to Issues</a>
    <a href="comment_list.php" class="btn btn-secondary mb-3">Back to Comments</a>

    <?php if (count($persons) > 0): ?>
        <table class="table table-bordered table-striped bg-white">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($persons as $person): ?>
                <tr>
                    <td><?php echo $person['id']; ?></td>
                    <td><?php echo htmlspecialchars($person['fname'] . ' ' . $person['lname']); ?></td>
                    <td><?php echo htmlspecialchars($person['mobile']); ?></td>
                    <td><?php echo htmlspecialchars($person['email']); ?></td>
                    <td><?php echo $person['admin'] == 1 ? '✔️' : ''; ?></td>
                    <td>
                        <a href="person.php?id=<?php echo $person['id']; ?>" class="btn btn-primary btn-sm">
                            View / Manage
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No people found.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
