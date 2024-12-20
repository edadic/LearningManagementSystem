<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // Insert into User table
    $query = "INSERT INTO User (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $name, $email, $password, $role);

    if ($stmt->execute()) {
        $userId = $stmt->insert_id; // Get the inserted user's ID

        // If the role is Teacher, insert into the Teacher table
        if ($role === 'Teacher') {
            $teacherQuery = "INSERT INTO Teacher (userId) VALUES (?)";
            $teacherStmt = $conn->prepare($teacherQuery);
            $teacherStmt->bind_param("i", $userId);
            $teacherStmt->execute();
        } elseif ($role === 'Student') {
            // If the role is Student, insert into the Student table
            $studentQuery = "INSERT INTO Student (userId) VALUES (?)";
            $studentStmt = $conn->prepare($studentQuery);
            $studentStmt->bind_param("i", $userId);
            $studentStmt->execute();
        } elseif ($role === 'Parent') {
            // If the role is Parent, insert into the Parent table
            $parentQuery = "INSERT INTO Parent (userId) VALUES (?)";
            $parentStmt = $conn->prepare($parentQuery);
            $parentStmt->bind_param("i", $userId);
            $parentStmt->execute();
        }

        echo "Registration successful!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>

<!-- Simple registration form -->
<!DOCTYPE html>
<html>

<head>
    <title>User Registration</title>
</head>

<body>
    <h2>Register</h2>
    <form method="POST" action="register.php">
        <label>Name:</label><br>
        <input type="text" name="name" required><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="Student">Student</option>
            <option value="Teacher">Teacher</option>
            <option value="Parent">Parent</option>
        </select><br><br>

        <button type="submit">Register</button>
    </form>
</body>

</html>