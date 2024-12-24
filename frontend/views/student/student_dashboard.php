<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header("Location: login.php");
    exit();
}

include '../../../backend/db.php';
$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Dashboard</title>
</head>

<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
    <h3>Student Dashboard</h3>

    <ul>
        <li><a href="../../../backend/api/attendance/view_attendance.php">View My Attendance</a></li>
        <li><a href="../../../backend/api/assignments/view_assignment.php">View Assignments</a></li>
        <li><a href="../../../backend/api/assignments/submit_assignment.php">Submit Assignment</a></li>
        <li><a href="../../../backend/api/grades/view_grades.php">View My Grades</a></li>
    </ul>

    <br>
    <a href="../../../backend/api/users/logout.php">Logout</a>
</body>

</html>
<?php $conn->close(); ?>