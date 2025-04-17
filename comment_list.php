<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    die("Not authorized.");
}

$user = $_SESSION['user'];

// Handle deletion if a delete request comes in
if (isset($_GET['delete_comment_id'])) {
    $comment_id = intval($_GET['delete_comment_id']);

    // Fetch the comment to verify permissions
    $stmt = $conn->prepare("SELECT * FROM iss_comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();

    if ($comment) {
        // Only allow admin or comment owner to delete
        if ($user['admin'] == 1 || $user['id'] == $comment['per_id']) {
            $deleteStmt = $conn->prepare("DELETE FROM iss_comments WHERE id = ?");
            $deleteStmt->bind_param("i", $comment_id);
            $deleteStmt->execute();
        } else {
            die("You do not have permission to delete this comment.");
        }
    }

    // Redirect to refresh the page and avoid re-executing on reload
    header("Location: comment_list.php");
    exit();
}

// Handling form submission to add a new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
    $iss_id = $_POST['iss_id'];
    $short_comment = $_POST['short_comment'];
    $long_comment = $_POST['long_comment'];
    $posted_date = date('Y-m-d H:i:s');  // You can set this as the current timestamp

    // Insert the new comment into the database
    $stmt = $conn->prepare("INSERT INTO iss_comments (per_id, iss_id, short_comment, long_comment, posted_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user['id'], $iss_id, $short_comment, $long_comment, $posted_date);

    if ($stmt->execute()) {
        header("Location: comment_list.php"); // Redirect to refresh the page and show the new comment
        exit();
    } else {
        die("Failed to add comment: " . $conn->error);
    }
}

// Fetch all comments
$stmt = $conn->prepare("SELECT c.id, c.short_comment, c.long_comment, c.posted_date, c.per_id, 
                        COALESCE(i.short_description, 'None') as short_description, 
                        p.fname, p.lname
                        FROM iss_comments c
                        LEFT JOIN iss_issues i ON c.iss_id = i.id
                        LEFT JOIN iss_persons p ON c.per_id = p.id");
$stmt->execute();
$comments_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments List</title>
    <!-- Bootstrap CSS for Modal and styling -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <style>
        /* Custom table styling */
        table {
            background-color: #f9f9f9;
            border-radius: 8px;
            overflow: hidden;
        }

        th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }

        td {
            text-align: center;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn-group .btn {
            margin: 0 5px;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
        }

        .modal-footer .btn {
            background-color: #007bff;
            color: white;
        }

        .container {
            max-width: 1200px;
        }

        /* Add some space at the bottom */
        .table-container {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2>Comments</h2>

    <!-- Button to Open the Add Comment Modal -->
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addCommentModal">Add Comment</button>

    <a href="issue_list.php" class="btn btn-secondary mb-3 ms-2">Go to Issue List</a>
    <a href="persons_list.php" class="btn btn-secondary mb-3 ms-2">Go to Person List</a>

    <!-- Comments Table -->
    <div class="table-container">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Short Comment</th>
                    <th>Long Comment</th>
                    <th>Issue</th>
                    <th>Posted By</th>
                    <th>Posted Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($comment['id']); ?></td>
                        <td><?php echo htmlspecialchars($comment['short_comment']); ?></td>
                        <td><?php echo htmlspecialchars($comment['long_comment']); ?></td>
                        <td><?php echo htmlspecialchars($comment['short_description']); ?></td>
                        <td>
                        <?php
                                if ($comment['fname'] === null && $comment['lname'] === null) {
                                    echo "[User Deleted]";
                                } else {
                                    echo htmlspecialchars($comment['fname'] . ' ' . $comment['lname']);
                                }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($comment['posted_date']); ?></td>
                        <td>
                            <?php if ($user['admin'] == 1 || $user['id'] == $comment['per_id']): ?>
                                <div class="btn-group">
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editCommentModal" data-id="<?php echo $comment['id']; ?>" data-short-comment="<?php echo htmlspecialchars($comment['short_comment']); ?>" data-long-comment="<?php echo htmlspecialchars($comment['long_comment']); ?>">Edit</button>
                                    <a href="comment_list.php?delete_comment_id=<?php echo $comment['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Comment Modal -->
    <div class="modal fade" id="addCommentModal" tabindex="-1" role="dialog" aria-labelledby="addCommentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCommentModalLabel">Add New Comment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Add Comment Form -->
                    <form action="comment_list.php" method="POST">
                        <input type="hidden" name="add_comment" value="1"> <!-- To identify the form submission -->
                        <div class="form-group">
                            <label for="issue">Issue</label>
                            <select name="iss_id" id="issue" class="form-control" required>
                                <?php
                                // Fetch issues for the dropdown
                                $issues_stmt = $conn->prepare("SELECT * FROM iss_issues");
                                $issues_stmt->execute();
                                $issues_result = $issues_stmt->get_result();

                                while ($issue = $issues_result->fetch_assoc()) {
                                    echo "<option value='" . $issue['id'] . "'>" . htmlspecialchars($issue['short_description']) . "</option>";
                                }
                                ?>
                                <option value="0">None</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="short_comment">Short Comment</label>
                            <input type="text" name="short_comment" id="short_comment" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="long_comment">Long Comment</label>
                            <textarea name="long_comment" id="long_comment" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Comment Modal -->
    <div class="modal fade" id="editCommentModal" tabindex="-1" role="dialog" aria-labelledby="editCommentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="edit_comment.php" method="POST">
                        <input type="hidden" name="comment_id" id="edit_comment_id">
                        <div class="form-group">
                            <label for="edit_short_comment">Short Comment</label>
                            <input type="text" name="short_comment" id="edit_short_comment" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_long_comment">Long Comment</label>
                            <textarea name="long_comment" id="edit_long_comment" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

<script>
    // Populate the Edit Modal with data
    $('#editCommentModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var commentId = button.data('id');
        var shortComment = button.data('short-comment');
        var longComment = button.data('long-comment');

        var modal = $(this);
        modal.find('#edit_comment_id').val(commentId);
        modal.find('#edit_short_comment').val(shortComment);
        modal.find('#edit_long_comment').val(longComment);
    });
</script>

</body>
</html>
