<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    die("Access denied. Only students can submit assignments.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file'])) {
    $assignmentId = $_POST['assignmentId'];
    $studentId = $_SESSION['user_id'];

    // File upload handling
    $targetDir = "uploads/";
    $fileName = basename($_FILES['file']['name']);
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath)) {
        // Insert submission into database
        $query = "INSERT INTO Submission (assignmentId, studentId, filePath) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $assignmentId, $studentId, $targetFilePath);

        if ($stmt->execute()) {
            echo "Assignment submitted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "File upload failed!";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Submit Assignment</title>
</head>

<body>
    <h2>Submit Assignment</h2>
    <form method="POST" action="submit_assignment.php" enctype="multipart/form-data">
        <label>Assignment ID:</label><br>
        <input type="number" name="assignmentId" required><br>

        <label>Choose File:</label><br>
        <input type="file" name="file" required><br><br>

        <button type="submit">Submit Assignment</button>
    </form>
    <br>
    <a href="../../../frontend/views/student/student_dashboard.php">Back to Dashboard</a>
</body>

</html>