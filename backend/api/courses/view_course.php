<?php
session_start();
include '../../db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

$query = "SELECT Course.courseId, Course.name, Course.description, User.name AS teacher_name 
          FROM Course 
          JOIN User ON Course.teacherId = User.id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Courses</title>
</head>

<body>
    <h2>Courses</h2>
    <table border="1">
        <tr>
            <th>Course ID</th>
            <th>Course Name</th>
            <th>Description</th>
            <th>Teacher</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['courseId'] . "</td>";
                echo "<td>" . $row['name'] . "</td>";
                echo "<td>" . $row['description'] . "</td>";
                echo "<td>" . $row['teacher_name'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No courses found</td></tr>";
        }
        ?>
    </table>
    <br>
    <a href="../../../frontend/views/teacher/teacher_dashboard.php">Back to Dashboard</a>
</body>

</html>
<?php $conn->close(); ?>