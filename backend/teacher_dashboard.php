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
    <a href="logout.php">Logout</a>
</body>

</html>