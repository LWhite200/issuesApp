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
    
    // Fetch animal details
    $stmt = $conn->prepare("SELECT * FROM animals WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $animal = $result->fetch_assoc();
    
    if (!$animal) {
        die("Animal not found.");
    }
} else {
    die("Animal ID is required.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($animal['name']); ?> Details</title>
</head>
<body>
    <h1>Animal Details</h1>

    <p>Name: <?php echo htmlspecialchars($animal['name']); ?></p>
    <p>Breed: <?php echo htmlspecialchars($animal['breed']); ?></p>
    <p>ID: <?php echo $animal['id']; ?></p>

    <?php if ($user && $user['admin'] == 1): ?>
        <p><a href="add_animal.php?edit=<?php echo $animal['id']; ?>">Edit Animal</a></p>
        <p><a href="index.php?delete=<?php echo $animal['id']; ?>" onclick="return confirm('Are you sure?')">Delete Animal</a></p>
    <?php endif; ?>

    <p><a href="crud.php">Back to Animal List</a></p>
</body>
</html>

<?php $conn->close(); ?>
