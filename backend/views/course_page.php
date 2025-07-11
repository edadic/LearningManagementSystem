<?php
session_start();
require_once '../db.php';

$courseId = $_GET['courseId'] ?? null;
if (!$courseId) {
    http_response_code(400);
    echo 'Course ID is required';
    exit;
}

$userRole = $_SESSION['role'] ?? null;
if (!$userRole) {
    http_response_code(403);
    echo 'Access denied';
    exit;
}

$db = getDbConnection();
$resources = $db->resources->find(['courseId' => $courseId])->toArray();
$assignments = $db->assignments->find(['courseId' => $courseId])->toArray();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Page</title>
    <style>
        /* Add your styles here */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .section {
            margin-bottom: 2rem;
        }
        .upload-form {
            margin-bottom: 1rem;
        }
        .resource-list, .assignment-list {
            list-style: none;
            padding: 0;
        }
        .resource-list li, .assignment-list li {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Course Materials for Course ID: <?php echo htmlspecialchars($courseId); ?></h1>

        <section class="section">
            <h2>Resources</h2>
            <?php if ($userRole === 'teacher'): ?>
                <form class="upload-form" id="resource-upload-form" enctype="multipart/form-data">
                    <input type="hidden" name="courseId" value="<?php echo htmlspecialchars($courseId); ?>">
                    <label for="week">Week:</label>
                    <input type="text" id="week" name="week" required>

                    <label for="file">File:</label>
                    <input type="file" id="file" name="file" required>

                    <button type="submit">Upload Resource</button>
                </form>
            <?php endif; ?>
            <ul class="resource-list">
                <?php foreach ($resources as $resource): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($resource['filePath']); ?>" target="_blank">
                            <?php echo htmlspecialchars(basename($resource['filePath'])); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="section">
            <h2>Assignments</h2>
            <?php if ($userRole === 'teacher'): ?>
                <form class="upload-form" id="assignment-upload-form" enctype="multipart/form-data">
                    <input type="hidden" name="courseId" value="<?php echo htmlspecialchars($courseId); ?>">
                    <label for="assignmentId">Assignment ID:</label>
                    <input type="text" id="assignmentId" name="assignmentId" required>

                    <label for="file">File:</label>
                    <input type="file" id="file" name="file" required>

                    <button type="submit">Upload Assignment</button>
                </form>
            <?php endif; ?>

            <?php if ($userRole === 'student'): ?>
                <form class="upload-form" id="assignment-upload-form" enctype="multipart/form-data">
                    <input type="hidden" name="courseId" value="<?php echo htmlspecialchars($courseId); ?>">
                    <label for="assignmentId">Assignment ID:</label>
                    <input type="text" id="assignmentId" name="assignmentId" required>

                    <label for="studentId">Student ID:</label>
                    <input type="text" id="studentId" name="studentId" required>

                    <label for="file">File:</label>
                    <input type="file" id="file" name="file" required>

                    <button type="submit">Upload Assignment</button>
                </form>
            <?php endif; ?>

            <ul class="assignment-list">
                <?php foreach ($assignments as $assignment): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($assignment['filePath']); ?>" target="_blank">
                            <?php echo htmlspecialchars(basename($assignment['filePath'])); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>

    <script>
        document.getElementById('resource-upload-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('../api/resources/upload_resource.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            alert(result.success ? 'Resource uploaded successfully!' : `Error: ${result.error}`);
            location.reload();
        });

        document.getElementById('assignment-upload-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('../api/assignments/upload_assignment.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            alert(result.success ? 'Assignment uploaded successfully!' : `Error: ${result.error}`);
            location.reload();
        });
    </script>
</body>
</html>
