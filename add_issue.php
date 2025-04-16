<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'] ?? null;
$person_id = $user['id']; // Person ID is the currently logged-in user's ID

// Handle Add or Update Issue (POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $short_description = $_POST['short_description'];
    $long_description = $_POST['long_description'];
    $open_date = date('Y-m-d');
    $priority = $_POST['priority']; // Simple string for priority
    $org = $_POST['org'];
    $project = $_POST['project'];

    // Handle the PDF upload
    $attachmentPath = "";  // Default value set to an empty string, not null

    if (isset($_FILES['pdf_attachment']) && $_FILES['pdf_attachment']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['pdf_attachment']['tmp_name'];
        $fileName = $_FILES['pdf_attachment']['name'];
        $fileSize = $_FILES['pdf_attachment']['size'];
        $fileType = $_FILES['pdf_attachment']['type'];

        // Get the file extension and validate it
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        if ($fileExtension !== 'pdf') {
            die("Only PDF files are allowed.");
        }

        // Validate file size (max 2 MB)
        if ($fileSize > 2 * 1024 * 1024) {
            die("File size exceeds 2 MB limit.");
        }

        // Generate a unique file name to avoid conflicts
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = './uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        // Create the uploads directory if it doesn't exist
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        // Move the uploaded file to the server
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $attachmentPath = $dest_path;  // Save the file path
        } else {
            die("Error moving the uploaded file.");
        }
    }

    // Add or update the issue in the database
    if (isset($_POST['update'])) {
        // Update existing issue
        $id = $_POST['id'];
        $query = "UPDATE iss_issues SET short_description=?, long_description=?, open_date=?, priority=?, org=?, project=?, per_id=?, pdf_attachment=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssisi", $short_description, $long_description, $open_date, $priority, $org, $project, $person_id, $attachmentPath, $id);
        $success = $stmt->execute();
    } else {
        // Add new issue
        $query = "INSERT INTO iss_issues (short_description, long_description, open_date, priority, org, project, per_id, pdf_attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssis", $short_description, $long_description, $open_date, $priority, $org, $project, $person_id, $attachmentPath);
        $success = $stmt->execute();
    }

    $_SESSION['message'] = $success ? "Action successful!" : "Error: " . $conn->error;
    header("Location: issue_list.php");
    exit();
}

// Fetch the issue to update (GET)
$edit_issue = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM iss_issues WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_issue = $result->fetch_assoc();
}

$conn->close();
?>
