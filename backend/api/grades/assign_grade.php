<?php
session_start();
include '../../db.php';

// Ensure only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    die("Access denied. Only teachers can assign grades.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignmentId = $_POST['assignmentId'];
    $studentId = $_POST['studentId'];
    $grade = $_POST['grade'];
    $comments = $_POST['comments'];

    // Insert or update grade
    $query = "INSERT INTO Grades (assignmentId, studentId, grade, comments)
              VALUES (?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE grade = VALUES(grade), comments = VALUES(comments)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $assignmentId, $studentId, $grade, $comments);

    if ($stmt->execute()) {
        echo "Grade assigned successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Assign Grade</title>
</head>

<body>
    <h2>Assign Grade</h2>
    <form method="POST" action="assign_grade.php">
        <label>Assignment ID:</label><br>
        <input type="number" name="assignmentId" required><br>

        <label>Student ID:</label><br>
        <input type="number" name="studentId" required><br>

        <label>Grade:</label><br>
        <input type="text" name="grade" maxlength="5" required><br>

        <label>Comments:</label><br>
        <textarea name="comments" rows="4"></textarea><br><br>

        <button type="submit">Assign Grade</button>
    </form>
    <br>
    <a href="../../../frontend/views/teacher/teacher_dashboard.php">Back to Dashboard</a>
</body>

</html>
<?php $conn->close(); ?>