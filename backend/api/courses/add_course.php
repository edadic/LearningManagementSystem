<?php
session_start();
include '../../db.php';

// Ensure only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: ../users/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $teacherId = $_SESSION['user_id']; // Logged-in teacher's ID

    $query = "INSERT INTO Course (name, description, teacherId) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssi", $name, $description, $teacherId);

    if ($stmt->execute()) {
        echo "Course added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>

<!-- Add Course Form -->
<!DOCTYPE html>
<html>

<head>
    <title>Add Course</title>
</head>

<body>
    <h2>Add a New Course</h2>
    <form method="POST" action="add_course.php">
        <label>Course Name:</label><br>
        <input type="text" name="name" required><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" required></textarea><br><br>

        <button type="submit">Add Course</button>
    </form>
    <br>
    <a href="../../../frontend/views/teacher/teacher_dashboard.php">Back to Dashboard</a>
</body>

</html>