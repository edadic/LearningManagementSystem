<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Teacher Dashboard</title>
</head>

<body>
    <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>
    <p>This is the Teacher Dashboard.</p>
    <a href="../../../backend/api/users/logout.php">Logout</a>

    <h3>Attendance Management</h3>
    <ul>
        <li><a href="../../../backend/api/attendance/mark_attendance.php">Mark Attendance</a></li>
        <li><a href="../../../backend/api/attendance/view_attendance.php">View Attendance Records</a></li>
    </ul>

    <h3>Grade Management</h3>
    <ul>
        <li><a href="../../../backend/api/grades/assign_grade.php">Assign Grades</a></li>
        <li><a href="../../../backend/api/grades/view_grades.php">View Grades</a></li>
    </ul>
</body>

</html>