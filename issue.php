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

    // Fetch issue details
    $stmt = $conn->prepare("SELECT * FROM iss_issues WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $issue = $result->fetch_assoc();

    if (!$issue) {
        die("Issue not found.");
    }

    // Fetch person details based on person ID
    $person_id = $issue['per_id'];
    $personStmt = $conn->prepare("SELECT fname, lname FROM iss_persons WHERE id = ?");
    $personStmt->bind_param("i", $person_id);
    $personStmt->execute();
    $personResult = $personStmt->get_result();
    $person = $personResult->fetch_assoc();

    // Fetch comments for the issue
    $commentStmt = $conn->prepare("SELECT * FROM iss_comments WHERE iss_id = ?");
    $commentStmt->bind_param("i", $id);
    $commentStmt->execute();
    $commentResult = $commentStmt->get_result();

} else {
    die("Issue ID is required.");
}

// Handle the deletion of a comment
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];

    // Check if the logged-in user is an admin or the author of the comment
    $commentStmt = $conn->prepare("SELECT per_id FROM iss_comments WHERE id = ?");
    $commentStmt->bind_param("i", $comment_id);
    $commentStmt->execute();
    $commentResult = $commentStmt->get_result();
    $comment = $commentResult->fetch_assoc();

    if (!$comment) {
        die("Comment not found.");
    }

    // Check if user is authorized to delete the comment
    if (($user && $user['admin'] == 1) || ($user && $user['id'] == $comment['per_id'])) {
        // Perform the deletion
        $deleteCommentStmt = $conn->prepare("DELETE FROM iss_comments WHERE id = ?");
        $deleteCommentStmt->bind_param("i", $comment_id);

        if ($deleteCommentStmt->execute()) {
            // Redirect to the same issue page to avoid resubmission
            header("Location: issue.php?id=$id");
            exit();
        } else {
            die("Error deleting comment: " . $conn->error);
        }
    } else {
        die("You do not have permission to delete this comment.");
    }
}


// Handle adding a new comment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['short_comment'], $_POST['long_comment'])) {
    $short_comment = $_POST['short_comment'];
    $long_comment = $_POST['long_comment'];
    $per_id = $_SESSION['user']['id']; // Get user ID from session
    $iss_id = $id; // Current issue ID
    $posted_date = date('Y-m-d H:i:s'); // Current date and time

    // Insert new comment into the database
    $insertStmt = $conn->prepare("INSERT INTO iss_comments (iss_id, per_id, short_comment, long_comment, posted_date) VALUES (?, ?, ?, ?, ?)");
    $insertStmt->bind_param("iisss", $iss_id, $per_id, $short_comment, $long_comment, $posted_date);

    if ($insertStmt->execute()) {
        // Redirect to the same page to avoid resubmission
        header("Location: issue.php?id=$id");
        exit();
    } else {
        echo "Error adding comment: " . $conn->error;
    }
}

// Handle the deletion
if (isset($_GET['delete']) && $_GET['delete'] == $id) {
    if (($user && $user['admin'] == 1) || ($user && $user['id'] == $issue['per_id'])) {
        // Perform deletion
        $deleteStmt = $conn->prepare("DELETE FROM iss_issues WHERE id = ?");
        $deleteStmt->bind_param("i", $id);

        if ($deleteStmt->execute()) {
            // Redirect to issue list after successful deletion, no query parameters
            header("Location: issue_list.php?message=Issue deleted successfully");
            exit();
        } else {
            die("Error deleting issue: " . $conn->error);
        }
    } else {
        die("You do not have permission to delete this issue.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($issue['short_description']); ?> Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1, h2 {
            color: #2e3d49;
            margin-bottom: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            padding: 12px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .card {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* New styles for comments */
        .comment-card {
            border: 2px solid #007BFF; /* Blue border */
            padding: 15px;
            background-color: #f1faff; /* Light blue background */
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .comment-card p {
            margin: 8px 0;
        }
        .card p {
            margin: 8px 0;
        }
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* This will be 80% of the screen width */
            max-width: 600px; /* Set a max width */
            position: relative; /* Keep this to position the close button */
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        /* Delete Button Styling */
        .delete-btn {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 2px 4px;
            text-align: center;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 10px;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="issue_list.php" class="button">Back to Issue List</a>
        <h1>Issue Details</h1>

        <div class="card">
            <p><strong>ID:</strong> <?php echo $issue['id']; ?></p>
            <p><strong>Short Description:</strong> <?php echo htmlspecialchars($issue['short_description']); ?></p>
            <p><strong>Long Description:</strong> <?php echo htmlspecialchars($issue['long_description']); ?></p>
            <p><strong>Open Date:</strong> <?php echo htmlspecialchars($issue['open_date']); ?></p>
            <p><strong>Closed Date:</strong> <?php echo htmlspecialchars($issue['closed_date'] ?? 'Open'); ?></p>
            <p><strong>Priority:</strong> <?php echo htmlspecialchars($issue['priority']); ?></p>
            <p><strong>Organization:</strong> <?php echo htmlspecialchars($issue['org']); ?></p>
            <p><strong>Project:</strong> <?php echo htmlspecialchars($issue['project']); ?></p>
            <?php if ($person): ?>
                <p><strong>Person:</strong> <a href="person.php?id=<?php echo $person_id; ?>"><?php echo htmlspecialchars($person['fname']) . ' ' . htmlspecialchars($person['lname']); ?></a></p>
            <?php else: ?>
                <p><strong>Person:</strong> Person deleted</p>
            <?php endif; ?>
        </div>

        <?php if (($user && $user['admin'] == 1) || ($user && $user['id'] == $issue['per_id'])): ?>
            <div class="card">
                <a href="edit_issue.php?id=<?php echo $issue['id']; ?>" class="button">Edit Issue</a>
                <a href="issue.php?id=<?php echo $issue['id']; ?>&delete=<?php echo $issue['id']; ?>" class="button">Delete Issue</a>
            </div>
        <?php endif; ?>

        <h2>Comments:</h2>
        <?php while ($comment = $commentResult->fetch_assoc()): ?>
            <?php
            // Fetch person details for the comment
            $commentPersonStmt = $conn->prepare("SELECT fname, lname FROM iss_persons WHERE id = ?");
            $commentPersonStmt->bind_param("i", $comment['per_id']);
            $commentPersonStmt->execute();
            $commentPersonResult = $commentPersonStmt->get_result();
            $commentPerson = $commentPersonResult->fetch_assoc();
            ?>
            <div class="card comment-card"> <!-- Added the 'comment-card' class here -->
            <p><strong>Comment by:</strong> <?php echo htmlspecialchars($commentPerson['fname'] . ' ' . $commentPerson['lname']); ?></p>
            <p><a href="#" class="comment-link" data-fullname="<?php echo htmlspecialchars($commentPerson['fname'] . ' ' . $commentPerson['lname']); ?>" data-longcomment="<?php echo htmlspecialchars($comment['long_comment']); ?>" data-posteddate="<?php echo htmlspecialchars($comment['posted_date']); ?>"><?php echo htmlspecialchars($comment['short_comment']); ?></a></p>

                <!-- Display Delete Button if User is Admin or is the Comment Author -->
                <?php if (($user && $user['admin'] == 1) || ($user && $user['id'] == $comment['per_id'])): ?>
                    <a href="issue.php?id=<?php echo $id; ?>&delete_comment=<?php echo $comment['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this comment?');">Delete Comment</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <button id="addCommentBtn" class="button">Add Comment</button>

        <!-- Modal for Adding Comment -->
        <div id="addCommentModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Add a Comment</h2>
                <form action="issue.php?id=<?php echo $id; ?>" method="POST">
                    <div class="form-group">
                        <label for="short_comment">Short Comment:</label>
                        <input type="text" name="short_comment" id="short_comment" required>
                    </div>
                    <div class="form-group">
                        <label for="long_comment">Long Comment:</label>
                        <textarea name="long_comment" id="long_comment" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="button">Submit Comment</button>
                </form>
            </div>
        </div>

        <!-- Modal for displaying full comment details -->
        <!-- Modal for displaying full comment details -->
        <div id="commentModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeCommentModal">&times;</span>
                <h2>Comment Details</h2>
                <p><strong>Comment by:</strong> <span id="commentPerson"></span></p> <!-- This will now display the full name -->
                <p><strong>Posted on:</strong> <span id="commentDate"></span></p>
                <p><strong>Full Comment:</strong></p>
                <p id="commentLong"></p>
            </div>
        </div>

    </div>

    <script>
        // Modal for adding comment
        // Modal for adding comment
        // Modal for adding comment
        var modal = document.getElementById("addCommentModal");
        var btn = document.getElementById("addCommentBtn");
        var span = document.getElementsByClassName("close")[0]; // Close button for the "Add Comment" modal

        // Show modal when the "Add Comment" button is clicked
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // Close the modal when the close button is clicked
        span.onclick = function() {
            modal.style.display = "none";
        }

        // Modal for viewing comment details
        var commentModal = document.getElementById("commentModal");
        var closeCommentModal = document.getElementById("closeCommentModal");

        // Handle clicks on comment links
        // Handle clicks on comment links
        document.querySelectorAll(".comment-link").forEach(function(link) {
            link.addEventListener("click", function(event) {
                event.preventDefault();

                // Get full name from the data-fullname attribute
                var commentPersonName = link.getAttribute("data-fullname");  // Fetch the full name
                var postedDate = link.getAttribute("data-posteddate");
                var longComment = link.getAttribute("data-longcomment");

                // Insert the full name into the modal
                document.getElementById("commentPerson").textContent = commentPersonName;  // Display the full name here
                document.getElementById("commentDate").textContent = postedDate;
                document.getElementById("commentLong").textContent = longComment;

                // Show the modal
                commentModal.style.display = "block";  
            });
        });


        // Close the comment modal
        closeCommentModal.onclick = function() {
            commentModal.style.display = "none";
        }

        // Close both modals when clicking outside of them
        window.onclick = function(event) {
            if (event.target == modal) {  // If clicking outside the "Add Comment" modal
                modal.style.display = "none";
            } else if (event.target == commentModal) {  // If clicking outside the comment modal
                commentModal.style.display = "none";
            }
        }


    </script>
</body>
</html>

<?php $conn->close(); ?>
