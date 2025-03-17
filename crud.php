<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

// Fetch all animals
$animals = $conn->query("SELECT * FROM animals")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animal List</title>
</head>
<body>
    <h1>All Animals</h1>

    <?php if ($user && $user['admin'] == 1): ?>
        <p><a href="add_animal.php">Add New Animal</a></p>
    <?php endif; ?>

    <?php if (count($animals) > 0): ?>
        <ul>
            <?php foreach ($animals as $animal): ?>
                <li>
                    <a href="animal.php?id=<?php echo $animal['id']; ?>"><?php echo htmlspecialchars($animal['name']); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No animals found. <a href="add_animal.php">Add a new animal</a></p>
    <?php endif; ?>
</body>
</html>

<?php $conn->close(); ?>
