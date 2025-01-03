<?php
session_start();
include '../../db.php';

// Fetch assignments
$query = "SELECT a.assignmentId, a.title, a.description, a.deadline, c.name AS course_name 
          FROM Assignment a
          JOIN Course c ON a.courseId = c.courseId";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Assignments</title>
</head>

<body>
    <h2>Assignments</h2>
    <table border="1">
        <tr>
            <th>Assignment ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Deadline</th>
            <th>Course</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['assignmentId'] . "</td>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>" . $row['deadline'] . "</td>";
                echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                echo "<td>
                    <a href='edit_assignment.php?id=" . $row['assignmentId'] . "'>Edit</a> | 
                    <a href='delete_assignment.php?id=" . $row['assignmentId'] . "' onclick=\"return confirm('Are you sure you want to delete this assignment?');\">Delete</a>
                </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No assignments found</td></tr>";
        }
        ?>
    </table>
    <br>
    <a href="../../../frontend/views/teacher/teacher_dashboard.php">Back to Dashboard</a>
</body>

</html>
<?php $conn->close(); ?>