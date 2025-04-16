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
            header("Location: persons_list.php?message=Person deleted successfully");

            exit();
        } else {
            die("Error deleting person: " . $conn->error);
        }
    } else {
        die("You do not have permission to delete this person.");
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
                        <!-- Trigger the modal -->
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editPersonModal">Edit Person</button>
                        <a href="person.php?id=<?php echo $person['id']; ?>&delete=<?php echo $person['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this person?');">Delete Person</a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Edit Person Modal -->
        <div class="modal fade" id="editPersonModal" tabindex="-1" aria-labelledby="editPersonModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPersonModalLabel">Edit Person</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editPersonForm" method="POST">
                            <div class="mb-3">
                                <label for="fname" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($person['fname']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="lname" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($person['lname']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($person['mobile']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($person['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="admin" class="form-label">Admin Status</label>
                                <input type="checkbox" class="form-check-input" id="admin" name="admin" <?php echo $person['admin'] == 1 ? 'checked' : ''; ?>>
                                <label for="admin" class="form-check-label">Make Admin</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Person</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Issue List Button -->
        <div class="mt-4">
            <a href="issue_list.php" class="btn btn-custom">Back to Issue List</a>
            <a href="persons_list.php" class="btn btn-custom">Back to Person List</a>
        </div>
    </div>

    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <script>
        document.getElementById("editPersonForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent the form from submitting normally

            const formData = new FormData(this);
            formData.append("id", <?php echo $person['id']; ?>);

            fetch("edit_person.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal and reload the page
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editPersonModal'));
                    modal.hide();
                    location.reload();  // Reload the page after successful update
                } else {
                    alert('Error updating person.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
