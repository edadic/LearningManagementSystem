<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    die("Access denied. Only teachers can add assignments.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseId = $_POST['courseId'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];

    $query = "INSERT INTO Assignment (courseId, title, description, deadline) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isss", $courseId, $title, $description, $deadline);

    if ($stmt->execute()) {
        echo "Assignment added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Assignment</title>
</head>

<body>
    <h2>Add Assignment</h2>
    <form method="POST" action="add_assignment.php">
        <label>Course ID:</label><br>
        <input type="number" name="courseId" required><br>

        <label>Title:</label><br>
        <input type="text" name="title" required><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" required></textarea><br>

        <label>Deadline:</label><br>
        <input type="datetime-local" name="deadline" required><br><br>

        <button type="submit">Add Assignment</button>
    </form>
    <br>
    <a href="../../../frontend/views/teacher/teacher_dashboard.php">Back to Dashboard</a>
</body>

</html>