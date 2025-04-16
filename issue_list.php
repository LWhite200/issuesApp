<!-- mark issues as resolved -->
<!-- edit comments -->

<!-- deleting people (only admins) -->

<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Fetch the issue to check permissions
    $result = $conn->query("SELECT * FROM iss_issues WHERE id = $delete_id");
    $issue = $result->fetch_assoc();

    // Check if the user is the one who created the issue or an admin
    if ($issue && ($user['id'] == $issue['per_id'] || $user['admin'] == 1)) {
        // Proceed with deletion
        $conn->query("DELETE FROM iss_issues WHERE id = $delete_id");
        header("Location: issue_list.php"); // Redirect after deletion
        exit();
    } else {
        // Unauthorized access
        echo "You are not authorized to delete this issue.";
    }
}

// Fetch all issues
$issues = $conn->query("SELECT * FROM iss_issues")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issues List</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <p><a href="logout.php">Logout Exit</a></p>
    <h1>All Issues</h1>

    <!-- Button to trigger Add Issue modal -->
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addIssueModal">
        Add New Issue
    </button>

    <!-- Go to Person List Button -->
    <a href="persons_list.php" class="btn btn-secondary ms-2">
        Go to Person List
    </a>

    <a href="comment_list.php" class="btn btn-secondary ms-2">
        Go to Comment List
    </a>

    <?php if (count($issues) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Description</th>
                    <th>Open Date</th>
                    <th>Close Date</th>
                    <th>Priority</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues as $issue): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($issue['id']); ?></td>
                        <td><a href="issue.php?id=<?php echo $issue['id']; ?>"><?php echo htmlspecialchars($issue['short_description']); ?></a></td>
                        <td><?php echo htmlspecialchars($issue['open_date']); ?></td>
                        <td>
                            <?php echo ($issue['close_date'] === '0000-00-00' || empty($issue['close_date'])) ? 'Unresolved' : htmlspecialchars($issue['close_date']); ?>
                        </td>
                        <td>
                            <?php echo ($issue['close_date'] === '0000-00-00' || empty($issue['close_date'])) ? htmlspecialchars($issue['priority']) : ''; ?>
                        </td>
                        <td>
                            <!-- Action Buttons -->
                            <a href="issue.php?id=<?php echo $issue['id']; ?>" class="btn btn-info">Read</a>

                            <?php 
                            // Only show the Edit link if the user is the one who uploaded the issue or if the user is an admin
                            if ($user['id'] == $issue['per_id'] || $user['admin'] == 1): ?>
                                <!-- Trigger Edit Issue modal -->
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editIssueModal" 
                                data-id="<?php echo $issue['id']; ?>"
                                data-short-description="<?php echo htmlspecialchars($issue['short_description']); ?>"
                                data-long-description="<?php echo htmlspecialchars($issue['long_description']); ?>"
                                data-open-date="<?php echo htmlspecialchars($issue['open_date']); ?>"
                                data-priority="<?php echo htmlspecialchars($issue['priority']); ?>"
                                data-org="<?php echo htmlspecialchars($issue['org']); ?>"
                                data-project="<?php echo htmlspecialchars($issue['project']); ?>"
                                >
                                    Edit
                                </button>
                                
                                <!-- Delete Button -->
                                <form action="issue_list.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $issue['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this issue?');">
                                        Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No issues found.</p>
    <?php endif; ?>

    <!-- Add Issue Modal -->
    <div class="modal fade" id="addIssueModal" tabindex="-1" aria-labelledby="addIssueModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addIssueModalLabel">Add New Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="add_issue.php" method="POST" enctype="multipart/form-data">
                        <label>Short Description: <input type="text" name="short_description" required class="form-control"></label><br>
                        <label>Long Description: <textarea name="long_description" required class="form-control"></textarea></label><br>
                        
                        <label>Priority:
                            <select name="priority" class="form-control" required>
                                <option value="">Select Priority</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                                <option value="E">E</option>
                            </select>
                        </label><br>
                        <label>Organization: <input type="text" name="org" required class="form-control"></label><br>
                        <label>Project: <input type="text" name="project" required class="form-control"></label><br>
                        <label>Attach PDF: <input type="file" name="pdf_attachment" accept="application/pdf" class="form-control"></label><br>
                        <button type="submit" class="btn btn-primary">Add Issue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Issue Modal -->
    <!-- Edit Issue Modal -->
    <div class="modal fade" id="editIssueModal" tabindex="-1" aria-labelledby="editIssueModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editIssueModalLabel">Edit Issue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="edit_issue.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="edit-id">
                        <label>Short Description: <input type="text" name="short_description" id="edit-short-description" required class="form-control"></label><br>
                        <label>Long Description: <textarea name="long_description" id="edit-long-description" required class="form-control"></textarea></label><br>
                        <label>Open Date: <input type="date" name="open_date" id="edit-open-date" required class="form-control"></label><br>
                        <label>Priority: <input type="text" name="priority" id="edit-priority" required class="form-control"></label><br>
                        <label>Organization: <input type="text" name="org" id="edit-org" required class="form-control"></label><br>
                        <label>Project: <input type="text" name="project" id="edit-project" required class="form-control"></label><br>
                        
                        <?php if ($issue['pdf_attachment']): ?>
                            <!-- Display existing PDF and option to remove it -->
                            <div>
                                <label>Current PDF: <a href="<?php echo htmlspecialchars($issue['pdf_attachment']); ?>" target="_blank">View PDF</a></label><br>
                                <label><input type="checkbox" name="remove_pdf" value="1"> Remove existing PDF</label><br>
                            </div>
                        <?php else: ?>
                            <!-- Option to upload a new PDF if none exists -->
                            <label>Attach PDF: <input type="file" name="pdf_attachment" accept="application/pdf" class="form-control"></label><br>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-warning">Update Issue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS & Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

    <script>
        // Populate edit modal with the issue data
        var editIssueModal = document.getElementById('editIssueModal');
        editIssueModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Button that triggered the modal
            var issueId = button.getAttribute('data-id');
            var shortDescription = button.getAttribute('data-short-description');
            var longDescription = button.getAttribute('data-long-description');
            var openDate = button.getAttribute('data-open-date');
            var priority = button.getAttribute('data-priority');
            var org = button.getAttribute('data-org');
            var project = button.getAttribute('data-project');

            // Populate the form fields
            document.getElementById('edit-id').value = issueId;
            document.getElementById('edit-short-description').value = shortDescription;
            document.getElementById('edit-long-description').value = longDescription;
            document.getElementById('edit-open-date').value = openDate;
            document.querySelector('#edit-priority').value = priority;

            document.getElementById('edit-org').value = org;
            document.getElementById('edit-project').value = project;
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
