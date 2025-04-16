<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    die("Not authorized.");
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment_id = $_POST['comment_id'];
    $short_comment = $_POST['short_comment'];
    $long_comment = $_POST['long_comment'];

    // Fetch comment to verify permissions
    $stmt = $conn->prepare("SELECT * FROM iss_comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $comment = $result->fetch_assoc();

    if (!$comment) {
        die("Comment not found.");
    }

    if ($user['admin'] != 1 && $user['id'] != $comment['per_id']) {
        die("You do not have permission to edit this comment.");
    }

    // Update the comment
    $update = $conn->prepare("UPDATE iss_comments SET short_comment = ?, long_comment = ? WHERE id = ?");
    $update->bind_param("ssi", $short_comment, $long_comment, $comment_id);
    if ($update->execute()) {
        header("Location: issue.php?id=" . $comment['iss_id']);
        exit();
    } else {
        die("Update failed: " . $conn->error);
    }
}
?>
