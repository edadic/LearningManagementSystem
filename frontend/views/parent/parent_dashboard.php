<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Parent') {
    header("Location: ../../../backend/api/users/login.php");
    exit();
}

include '../../../backend/db.php';
$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Parent Dashboard</title>
</head>

<body>
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
    <h3>Parent Dashboard</h3>

    <ul>
        <li><a href="../../../backend/api/attendance/view_attendance.php">View My Child's Attendance</a></li>
        <li><a href="../../../backend/api/grades/view_grades.php">View My Child's Grades</a></li>
    </ul>

    <br>
    <a href="../../../backend/api/users/logout.php">Logout</a>
</body>

</html>
<?php $conn->close(); ?>