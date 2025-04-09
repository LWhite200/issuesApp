<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;

if ($user['admin'] != 1) {
    die("You do not have permission to edit this issue.");
}

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

// Update issue details when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $priority = $_POST['priority'];
    $org = $_POST['org'];
    $project = $_POST['project'];
    $per_id = $_POST['per_id'];  // Person ID who is responsible for the issue

    // Handle new PDF upload or removal
    if (isset($_FILES['pdf_attachment']) && $_FILES['pdf_attachment']['error'] == 0) {
        // New PDF uploaded, validate and move it
        $file_tmp = $_FILES['pdf_attachment']['tmp_name'];
        $file_name = $_FILES['pdf_attachment']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);

        if ($file_ext == 'pdf') {
            // Generate a unique file name using md5
            $new_pdf_name = md5(time() . $file_name) . '.' . $file_ext;
            $new_pdf_path = './uploads/' . $new_pdf_name;

            // Create the uploads directory if it doesn't exist
            if (!is_dir('./uploads')) {
                mkdir('./uploads', 0755, true);
            }

            // Move the uploaded file to the server
            move_uploaded_file($file_tmp, $new_pdf_path);

            // Delete the old PDF if it exists
            if ($issue['pdf_attachment'] && file_exists($issue['pdf_attachment'])) {
                unlink($issue['pdf_attachment']);  // Remove the old file
            }

            $pdf_attachment = $new_pdf_path;  // Update PDF path in the database
        } else {
            die("Invalid file type. Only PDF files are allowed.");
        }
    } elseif (isset($_POST['remove_pdf']) && $_POST['remove_pdf'] == '1') {
        // PDF is being removed
        if ($issue['pdf_attachment'] && file_exists($issue['pdf_attachment'])) {
            unlink($issue['pdf_attachment']);  // Delete existing file
        }
        $pdf_attachment = '';  // Clear the attachment (set it to empty string)
    } else {
        // No file uploaded and no removal flag set, keep the current attachment
        $pdf_attachment = $issue['pdf_attachment'];  // Retain current PDF
    }

    // Update the issue information including the PDF attachment
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
