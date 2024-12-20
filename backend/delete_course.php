<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $courseId = $_GET['id'];

    $query = "DELETE FROM Course WHERE courseId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $courseId);

    if ($stmt->execute()) {
        echo "Course deleted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
header("Location: view_courses.php");
exit();
