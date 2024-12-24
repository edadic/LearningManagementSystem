<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

// Fetch attendance records based on role
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'Teacher') {
    // Teachers: Filter by course if provided
    $selectedCourse = isset($_GET['courseId']) ? $_GET['courseId'] : null;

    $query = "SELECT a.attendanceId, a.date, a.status, s.studentId, u.name AS student_name, c.name AS course_name
              FROM Attendance a
              JOIN Student s ON a.studentId = s.studentId
              JOIN User u ON s.userId = u.id
              JOIN Course c ON a.courseId = c.courseId
              WHERE c.teacherId = ?";

    // Append course filter
    if ($selectedCourse) {
        $query .= " AND c.courseId = ?";
    }

    $stmt = $conn->prepare($query);

    if ($selectedCourse) {
        $stmt->bind_param("ii", $userId, $selectedCourse);
    } else {
        $stmt->bind_param("i", $userId);
    }
} elseif ($role == 'Student') {
    // Students: View their own attendance
    $query = "SELECT a.date, a.status, c.name AS course_name
              FROM Attendance a
              JOIN Course c ON a.courseId = c.courseId
              WHERE a.studentId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
} elseif ($role == 'Parent') {
    // Parents: View their child's attendance
    $query = "SELECT a.date, a.status, c.name AS course_name, u.name AS student_name
              FROM Attendance a
              JOIN Student s ON a.studentId = s.studentId
              JOIN User u ON s.userId = u.id
              JOIN Course c ON a.courseId = c.courseId
              WHERE s.studentId = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
} else {
    die("Invalid role.");
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Attendance</title>
</head>

<body>
    <h2>Attendance Records</h2>

    <?php if ($role == 'Teacher') : ?>
        <!-- Course Filter for Teachers -->
        <form method="GET" action="view_attendance.php">
            <label for="courseId">Select Course:</label>
            <select name="courseId" id="courseId">
                <option value="">All Courses</option>
                <?php
                // Fetch courses taught by the teacher
                $courseQuery = "SELECT courseId, name FROM Course WHERE teacherId = ?";
                $courseStmt = $conn->prepare($courseQuery);
                $courseStmt->bind_param("i", $userId);
                $courseStmt->execute();
                $courseResult = $courseStmt->get_result();

                while ($course = $courseResult->fetch_assoc()) {
                    $selected = isset($_GET['courseId']) && $_GET['courseId'] == $course['courseId'] ? 'selected' : '';
                    echo "<option value='" . $course['courseId'] . "' $selected>" . htmlspecialchars($course['name']) . "</option>";
                }
                $courseStmt->close();
                ?>
            </select>
            <button type="submit">Filter</button>
        </form>
    <?php endif; ?>

    <!-- Attendance Table -->
    <table border="1">
        <tr>
            <th>Date</th>
            <th>Status</th>
            <th>Course</th>
            <?php if ($role == 'Teacher' || $role == 'Parent') echo "<th>Student</th>"; ?>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['course_name']) . "</td>";
                if ($role == 'Teacher' || $role == 'Parent') {
                    echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No attendance records found.</td></tr>";
        }
        ?>
    </table>

    <br>
    <a href="<?php echo strtolower($role) . '_dashboard.php'; ?>">Back to Dashboard</a>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>