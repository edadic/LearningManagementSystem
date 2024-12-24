<?php
session_start();
include '../../db.php';

// Ensure the user is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    die("Access denied. Only teachers can delete assignments.");
}

if (!isset($_GET['id'])) {
    die("Assignment ID not provided.");
}

$assignmentId = $_GET['id'];

// Delete the assignment
$query = "DELETE FROM Assignment WHERE assignmentId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $assignmentId);

if ($stmt->execute()) {
    echo "Assignment deleted successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
header("Location: view_assignments.php");
exit();
