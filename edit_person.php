<?php
include 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user = $_SESSION['user'] ?? null;

// Ensure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$person_id = $_POST['id'] ?? null;
if (!$person_id) {
    echo json_encode(['success' => false, 'message' => 'Person ID is missing']);
    exit();
}

// Check permission: admin or the same user
if (!($user['admin'] == 1 || $user['id'] == $person_id)) {
    echo json_encode(['success' => false, 'message' => 'Permission denied']);
    exit();
}

// Sanitize and collect input
$fname = $_POST['fname'] ?? '';
$lname = $_POST['lname'] ?? '';
$mobile = $_POST['mobile'] ?? '';
$email = $_POST['email'] ?? '';
$admin = isset($_POST['admin']) ? 1 : 0;

// Update
include 'config.php'; // if not already included

$updateStmt = $conn->prepare("UPDATE iss_persons SET fname = ?, lname = ?, mobile = ?, email = ?, admin = ? WHERE id = ?");
$updateStmt->bind_param("ssssii", $fname, $lname, $mobile, $email, $admin, $person_id);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}
?>
