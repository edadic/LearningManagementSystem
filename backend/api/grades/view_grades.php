<?php
session_start();
include '../../db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Students view their own grades, parents view their child's grades
if ($role == 'Student') {
    $query = "SELECT g.grade, a.title AS assignment_title
              FROM Grades g
              JOIN Assignment a ON g.assignmentId = a.assignmentId
              JOIN Student s ON g.studentId = s.studentId
              WHERE s.userId = ?";
} elseif ($role == 'Parent') {
    $query = "SELECT g.grade, a.title AS assignment_title, u.name AS student_name
              FROM Grades g
              JOIN Assignment a ON g.assignmentId = a.assignmentId
              JOIN Student s ON g.studentId = s.studentId
              JOIN User u ON s.userId = u.id
              WHERE s.userId = ?";
} else {
    die("Access denied.");
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Grades</title>
</head>

<body>
    <h2>Grades</h2>
    <table border="1">
        <tr>
            <th>Assignment</th>
            <th>Grade</th>
            <?php if ($role == 'Parent') echo "<th>Student</th>"; ?>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['assignment_title']); ?></td>
                <td><?php echo htmlspecialchars($row['grade']); ?></td>
                <?php if ($role == 'Parent') echo "<td>" . htmlspecialchars($row['student_name']) . "</td>"; ?>
            </tr>
        <?php } ?>
    </table>
    <br>
    <a href="<?php echo strtolower($role) . '_dashboard.php'; ?>">Back to Dashboard</a>
</body>

</html>
<?php $stmt->close();
$conn->close(); ?>