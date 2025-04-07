<!-- Only delete and update need security as create doesn't do anything -->
<!-- php cannot be edited by inspect but html can be -->
<!-- so check things in the php if they are admin or user == user -->

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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 900px;
            margin-top: 50px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-size: 1.25rem;
        }
        .card-body {
            background-color: white;
            padding: 30px;
        }
        .btn-custom {
            background-color: #6c757d;
            color: white;
        }
        .btn-custom:hover {
            background-color: #5a6268;
        }
        .admin-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 1rem;
        }
        .section-title {
            color: #343a40;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .action-buttons a {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Person Details</h1>

        <div class="card">
            <div class="card-header">
                Personal Information
            </div>
            <div class="card-body">
                <!-- Display Admin Badge for all profiles -->
                <?php if ($person['admin'] == 1): ?>
                    <div class="admin-badge mb-4">
                        <strong>Admin</strong>
                    </div>
                <?php endif; ?>

                <div class="section-title">Profile Info</div>
                <p><strong>ID:</strong> <?php echo $person['id']; ?></p>
                <p><strong>First Name:</strong> <?php echo htmlspecialchars($person['fname']); ?></p>
                <p><strong>Last Name:</strong> <?php echo htmlspecialchars($person['lname']); ?></p>
                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($person['mobile']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($person['email']); ?></p>

                <!-- Action buttons -->
                <div class="action-buttons mt-4">
                    <?php if ($user && ($user['admin'] == 1 || $user['id'] == $person['id'])): ?>
                        <a href="edit_person.php?id=<?php echo $person['id']; ?>" class="btn btn-warning">Edit Person</a>
                        <a href="person.php?id=<?php echo $person['id']; ?>&delete=<?php echo $person['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this person?');">Delete Person</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Back to Issue List Button -->
        <div class="mt-4">
            <a href="issue_list.php" class="btn btn-custom">Back to Issue List</a>
        </div>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>
