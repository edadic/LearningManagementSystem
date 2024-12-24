<?php
session_start();
include 'db.php';

// Ensure the user is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    die("Access denied. Only teachers can mark attendance.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseId = $_POST['courseId'];
    $studentId = $_POST['studentId'];
    $status = $_POST['status'];
    $date = $_POST['date'];

    // Insert attendance record
    $query = "INSERT INTO Attendance (courseId, studentId, status, date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $courseId, $studentId, $status, $date);

    if ($stmt->execute()) {
        echo "Attendance marked successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Mark Attendance</title>
</head>

<body>
    <h2>Mark Attendance</h