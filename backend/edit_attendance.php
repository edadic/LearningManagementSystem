<?php
session_start();
include 'db.php';

// Ensure the user is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    die("Access denied. Only teachers can edit attendance.");
}

// Check if attendance ID is provided
if (!isset($_GET['id'])) {
    die("Attendance ID not provided.");
}

$attendanceId = $_GET['id'];

// Fetch existing attendance details
$query = "SELECT * FROM Attendance WHERE attendanceId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $attendanceId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Attendance record not found.");
}
$attendance = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST['status'];
    $date = $_POST['date'];

    // Update attendance record
    $updateQuery = "UPDATE Attendance SET status = ?, date = ? WHERE attendanceId = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssi", $status, $date, $attendanceId);

    if ($updateStmt->execute()) {
        echo "Attendance updated successfully!";
    } else {
        echo "Error: " . $updateStmt->error;
    }
    $updateStmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Attendance</title>
</head>

<body>
    <h2>Edit Attendance</h2>
    <form method="POST" action="edit_attendance.php?id=<?php echo $attendanceId; ?>">
        <label>Status:</label><br>
        <select name="status" required>
            <option value="Present" <?php if ($attendance['status'] == 'Present') echo 'selected'; ?>>Present</option>
            <option value="Absent" <?php if ($attendance['status'] == 'Absent') echo 'selected'; ?>>Absent</option>
            <option value="Late" <?php if ($attendance['status'] == 'Late') echo 'selected'; ?>>Late</option>
        </select><br>

        <label>Date:</label><br>
        <input type="date" name="date" value="<?php echo $attendance['date']; ?>" required><br><br>

        <button type="submit">Update Attendance</button>
    </form>
    <br>
    <a href="view_attendance.php">Back to Attendance Records</a>
</body>

</html>