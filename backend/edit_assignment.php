<?php
session_start();
include 'db.php';

// Ensure the user is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    die("Access denied. Only teachers can edit assignments.");
}

// Check if assignment ID is provided
if (!isset($_GET['id'])) {
    die("Assignment ID not provided.");
}

$assignmentId = $_GET['id'];

// Fetch assignment details
$query = "SELECT * FROM Assignment WHERE assignmentId = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $assignmentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Assignment not found.");
}

$assignment = $result->fetch_assoc();

// Update assignment details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];

    $updateQuery = "UPDATE Assignment SET title = ?, description = ?, deadline = ? WHERE assignmentId = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssi", $title, $description, $deadline, $assignmentId);

    if ($updateStmt->execute()) {
        echo "Assignment updated successfully!";
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
    <title>Edit Assignment</title>
</head>

<body>
    <h2>Edit Assignment</h2>
    <form method="POST" action="edit_assignment.php?id=<?php echo $assignmentId; ?>">
        <label>Title:</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($assignment['title']); ?>" required><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" required><?php echo htmlspecialchars($assignment['description']); ?></textarea><br>

        <label>Deadline:</label><br>
        <input type="datetime-local" name="deadline" value="<?php echo date('Y-m-d\TH:i', strtotime($assignment['deadline'])); ?>" required><br><br>

        <button type="submit">Update Assignment</button>
    </form>
    <br>
    <a href="view_assignments.php">Back to Assignments</a>
</body>

</html>