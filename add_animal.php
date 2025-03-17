<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] != 1) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

// Handle Add or Update Animal (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $breed = $_POST['breed'];

    if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $query = "UPDATE animals SET name=?, breed=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $name, $breed, $id);
        $success = $stmt->execute();
    } else {
        $query = "INSERT INTO animals (name, breed) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $name, $breed);
        $success = $stmt->execute();
    }

    $_SESSION['message'] = $success ? "Action successful!" : "Error: " . $conn->error;
    header("Location: crud.php");
    exit();
}

// Fetch the animal to update (GET)
$edit_animal = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM animals WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_animal = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($edit_animal) ? "Edit Animal" : "Add Animal"; ?></title>
</head>
<body>
    <h1><?php echo isset($edit_animal) ? "Edit Animal" : "Add Animal"; ?></h1>

    <form action="" method="POST">
        <input type="hidden" name="id" value="<?php echo $edit_animal['id'] ?? ''; ?>">
        <label>Name: <input type="text" name="name" value="<?php echo $edit_animal['name'] ?? ''; ?>" required></label><br>
        <label>Breed: <input type="text" name="breed" value="<?php echo $edit_animal['breed'] ?? ''; ?>" required></label><br>
        <button type="submit" name="<?php echo isset($edit_animal) ? 'update' : 'add'; ?>">
            <?php echo isset($edit_animal) ? 'Update Animal' : 'Add Animal'; ?>
        </button>
    </form>

    <p><a href="crud.php">Back to Animal List</a></p>
</body>
</html>

<?php $conn->close(); ?>
