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
    <style>
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
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Additional form styling */
        label {
            display: block;
            margin: 10px 0 5px;
        }

        input, textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Issue Details</h1>

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

    <?php if (($user && $user['admin'] == 1) || ($user && $user['id'] == $issue['per_id'])): ?>
        <p><a href="edit_issue.php?id=<?php echo $issue['id']; ?>">Edit Issue</a></p>
        <p><a href="issue.php?id=<?php echo $issue['id']; ?>&delete=<?php echo $issue['id']; ?>">Delete Issue</a></p>
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
        <div>
            <p><strong>Comment by: </strong><?php echo htmlspecialchars($commentPerson['fname'] . ' ' . $commentPerson['lname']); ?></p>
            <p><a href="#" class="comment-link" data-id="<?php echo $comment['id']; ?>" data-perid="<?php echo $comment['per_id']; ?>" data-longcomment="<?php echo htmlspecialchars($comment['long_comment']); ?>" data-posteddate="<?php echo htmlspecialchars($comment['posted_date']); ?>"><?php echo htmlspecialchars($comment['short_comment']); ?></a></p>
        </div>
    <?php endwhile; ?>

    <button id="addCommentBtn">Add Comment</button>

    <p><a href="issue_list.php">Back to Issue List</a></p>

    <!-- Modal for Adding Comment -->
    <div id="addCommentModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add a Comment</h2>
            <form action="issue.php?id=<?php echo $id; ?>" method="POST">
                <label for="short_comment">Short Comment:</label>
                <input type="text" name="short_comment" id="short_comment" required>

                <label for="long_comment">Long Comment:</label>
                <textarea name="long_comment" id="long_comment" rows="4" required></textarea>

                <button type="submit">Submit Comment</button>
            </form>
        </div>
    </div>

    <!-- Modal for displaying full comment details -->
    <div id="commentModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeCommentModal">&times;</span>
            <h2>Comment Details</h2>
            <p><strong>Comment by:</strong> <span id="commentPerson"></span></p>
            <p><strong>Posted on:</strong> <span id="commentDate"></span></p>
            <p><strong>Full Comment:</strong></p>
            <p id="commentLong"></p>
        </div>
    </div>

    <script>
        // Modal for adding comment
        var modal = document.getElementById("addCommentModal");
        var btn = document.getElementById("addCommentBtn");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Modal for viewing comment details
        var commentModal = document.getElementById("commentModal");
        var closeCommentModal = document.getElementById("closeCommentModal");

        document.querySelectorAll(".comment-link").forEach(function(link) {
            link.addEventListener("click", function(event) {
                event.preventDefault();

                var commentPerson = link.getAttribute("data-perid");
                var postedDate = link.getAttribute("data-posteddate");
                var longComment = link.getAttribute("data-longcomment");

                document.getElementById("commentPerson").textContent = commentPerson; // Replace with logic to fetch person's name if needed
                document.getElementById("commentDate").textContent = postedDate;
                document.getElementById("commentLong").textContent = longComment;

                commentModal.style.display = "block";
            });
        });

        closeCommentModal.onclick = function() {
            commentModal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == commentModal) {
                commentModal.style.display = "none";
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
