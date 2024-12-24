<?php
session_start();
include 'db.php';

// Ensure the user is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    die("Access denied. Only teachers can delete attendance records.");
}

if (!isset($_GET['id'])) {
    die("Attendance ID not provided.");
}

$attendanceId = $_GET['id'];

// Delete attendance record
$query = "DELETE FROM Attendance WHERE attendanceId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $attendanceId);

if ($stmt->execute()) {
    echo "Attendance record deleted successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
header("Location: view_attendance.php");
exit();
