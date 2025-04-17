<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

if (!isset($_POST['id'])) {
    die("Issue ID is required.");
}

$issue_id = $_POST['id'];  // Get issue_id from the POST request

// Fetch the issue details
$stmt = $conn->prepare("SELECT * FROM iss_issues WHERE id = ?");
$stmt->bind_param("i", $issue_id);
$stmt->execute();
$result = $stmt->get_result();
$issue = $result->fetch_assoc();

if (!$issue) {
    die("Issue not found.");
}

// ✅ Permission check AFTER fetching issue
if ($user['admin'] != 1 && $user['id'] != $issue['per_id']) {
    die("You do not have permission to edit this issue.");
}

// Update issue details when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];

    // 🔐 Preserve original per_id unless admin is updating it
    if ($user['admin'] == 1 && isset($_POST['per_id']) && $_POST['per_id'] !== '') {
        $per_id = $_POST['per_id'];
    } else {
        $per_id = $issue['per_id'];
    }

    // Handle new PDF upload or removal
    if (isset($_FILES['pdf_attachment']) && $_FILES['pdf_attachment']['error'] == 0) {
        $file_tmp = $_FILES['pdf_attachment']['tmp_name'];
        $file_name = $_FILES['pdf_attachment']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if ($file_ext === 'pdf') {
            $new_pdf_name = md5(time() . $file_name) . '.' . $file_ext;
            $new_pdf_path = './uploads/' . $new_pdf_name;

            if (!is_dir('./uploads')) {
                mkdir('./uploads', 0755, true);
            }

            move_uploaded_file($file_tmp, $new_pdf_path);

            if ($issue['pdf_attachment'] && file_exists($issue['pdf_attachment'])) {
                unlink($issue['pdf_attachment']);
            }

            $pdf_attachment = $new_pdf_path;
        } else {
            die("Invalid file type. Only PDF files are allowed.");
        }
    } elseif (isset($_POST['remove_pdf']) && $_POST['remove_pdf'] == '1') {
        if ($issue['pdf_attachment'] && file_exists($issue['pdf_attachment'])) {
            unlink($issue['pdf_attachment']);
        }
        $pdf_attachment = '';
    } else {
        $pdf_attachment = $issue['pdf_attachment'];
    }

    // Update the issue
    $updateStmt = $conn->prepare("UPDATE iss_issues SET short_description = ?, long_description = ?, priority = ?, org = ?, project = ?, per_id = ?, pdf_attachment = ? WHERE id = ?");
    $updateStmt->bind_param("sssssssi", $short_description, $long_description, $priority, $org, $project, $per_id, $pdf_attachment, $issue_id);

    if ($updateStmt->execute()) {
        header("Location: issue_list.php?id=$issue_id&message=Issue updated successfully");
        exit();
    } else {
        die("Error updating issue: " . $conn->error);
    }
}

$conn->close();
?>
