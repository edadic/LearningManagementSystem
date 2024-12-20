<?php
session_start();
include 'db.php';

// Ensure only teachers can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $courseId = $_GET['id'];

    // Fetch existing course details
    $query = "SELECT * FROM Course WHERE courseId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['name'];
        $description = $_POST['description'];

        // Update the course
        $updateQuery = "UPDATE Course SET name = ?, description = ? WHERE courseId = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("ssi", $name, $description, $courseId);

        if ($updateStmt->execute()) {
            echo "Course updated successfully!";
        } else {
            echo "Error: " . $updateStmt->error;
        }
    }
} else {
    echo "Course ID not provided!";
    exit();
}
?>

<!-- Edit Course Form -->
<!DOCTYPE html>
<html>

<head>
    <title>Edit Course</title>
</head>

<body>
    <h2>Edit Course</h2>
    <form method="POST" action="edit_course.php?id=<?php echo $courseId; ?>">
        <label>Course Name:</label><br>
        <input type="text" name="name" value="<?php echo $course['name']; ?>" required><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" required><?php echo $course['description']; ?></textarea><br><br>

        <button type="submit">Update Course</button>
    </form>
    <br>
    <a href="view_courses.php">Back to Courses</a>
</body>

</html>
<?php $stmt->close();
$conn->close(); ?>