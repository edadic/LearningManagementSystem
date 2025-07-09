<?php
session_start();
require_once '../../db.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $database = connectToMongoDB();
    $submissionsCollection = $database->submissions;
    $assignmentsCollection = $database->assignments;
    $coursesCollection = $database->courses;
    $usersCollection = $database->users;
    
    $method = $_SERVER['REQUEST_METHOD'];
    $teacher_id = $_SESSION['user_id'];
    
    if ($method === 'GET') {
        // Get submissions that need grading for teacher's assignments
        if (isset($_GET['assignment_id'])) {
            // Get submissions for specific assignment
            $assignment_id = $_GET['assignment_id'];
            
            // Verify assignment belongs to this teacher
            try {
                $assignment = $assignmentsCollection->findOne([
                    '_id' => new MongoDB\BSON\ObjectId($assignment_id),
                    'teacher_id' => $teacher_id
                ]);
            } catch (Exception $e) {
                // If ObjectId conversion fails, try as string
                $assignment = $assignmentsCollection->findOne([
                    '_id' => $assignment_id,
                    'teacher_id' => $teacher_id
                ]);
            }
            
            if (!$assignment) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied to this assignment']);
                exit();
            }
            
            // Get all submissions for this assignment
            $submissions = $submissionsCollection->find(['assignment_id' => $assignment_id]);
            $submissionsList = [];
            
            foreach ($submissions as $submission) {
                // Get student details
                try {
                    $student = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($submission['student_id'])]);
                } catch (Exception $e) {
                    $student = $usersCollection->findOne(['_id' => $submission['student_id']]);
                }
                
                if ($student) {
                    $submissionsList[] = [
                        'submission_id' => (string)$submission['_id'],
                        'assignment_id' => $submission['assignment_id'],
                        'assignment_title' => $assignment['title'],
                        'student_id' => $submission['student_id'],
                        'student_name' => $student['name'],
                        'content' => $submission['content'],
                        'submitted_at' => isset($submission['submitted_at']) ? $submission['submitted_at']->toDateTime()->format('Y-m-d H:i:s') : null,
                        'grade' => $submission['grade'] ?? null,
                        'feedback' => $submission['feedback'] ?? '',
                        'graded_at' => isset($submission['graded_at']) ? $submission['graded_at']->toDateTime()->format('Y-m-d H:i:s') : null,
                        'max_points' => $assignment['max_points'] ?? 100
                    ];
                }
            }
            
            echo json_encode(['submissions' => $submissionsList]);
            
        } else {
            // Get all assignments for this teacher that have submissions
            $assignments = $assignmentsCollection->find(['teacher_id' => $teacher_id]);
            $assignmentsList = [];
            
            foreach ($assignments as $assignment) {
                $submissionCount = $submissionsCollection->countDocuments(['assignment_id' => (string)$assignment['_id']]);
                $gradedCount = $submissionsCollection->countDocuments([
                    'assignment_id' => (string)$assignment['_id'],
                    'grade' => ['$exists' => true]
                ]);
                
                // Get course details
                try {
                    $course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($assignment['course_id'])]);
                } catch (Exception $e) {
                    $course = $coursesCollection->findOne(['_id' => $assignment['course_id']]);
                }
                
                $assignmentsList[] = [
                    'assignment_id' => (string)$assignment['_id'],
                    'assignment_title' => $assignment['title'],
                    'course_id' => $assignment['course_id'],
                    'course_title' => $course ? ($course['title'] ?? $course['course_name']) : 'Unknown Course',
                    'max_points' => $assignment['max_points'] ?? 100,
                    'due_date' => isset($assignment['due_date']) ? $assignment['due_date']->toDateTime()->format('Y-m-d') : null,
                    'submission_count' => $submissionCount,
                    'graded_count' => $gradedCount,
                    'pending_count' => $submissionCount - $gradedCount
                ];
            }
            
            echo json_encode(['assignments' => $assignmentsList]);
        }
        
    } else if ($method === 'POST') {
        // Grade a submission
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['submission_id']) || !isset($input['grade'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields (submission_id, grade)']);
            exit();
        }
        
        $submission_id = $input['submission_id'];
        $grade = (float)$input['grade'];
        $feedback = $input['feedback'] ?? '';
        
        // Get the submission
        try {
            $submission = $submissionsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($submission_id)]);
        } catch (Exception $e) {
            $submission = $submissionsCollection->findOne(['_id' => $submission_id]);
        }
        
        if (!$submission) {
            http_response_code(404);
            echo json_encode(['error' => 'Submission not found']);
            exit();
        }
        
        // Verify assignment belongs to this teacher
        try {
            $assignment = $assignmentsCollection->findOne([
                '_id' => new MongoDB\BSON\ObjectId($submission['assignment_id']),
                'teacher_id' => $teacher_id
            ]);
        } catch (Exception $e) {
            $assignment = $assignmentsCollection->findOne([
                '_id' => $submission['assignment_id'],
                'teacher_id' => $teacher_id
            ]);
        }
        
        if (!$assignment) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied to this assignment']);
            exit();
        }
        
        // Update the submission with grade and feedback
        $updateData = [
            'grade' => $grade,
            'feedback' => $feedback,
            'graded_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        try {
            $result = $submissionsCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($submission_id)],
                ['$set' => $updateData]
            );
        } catch (Exception $e) {
            $result = $submissionsCollection->updateOne(
                ['_id' => $submission_id],
                ['$set' => $updateData]
            );
        }
        
        if ($result->getModifiedCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Grade assigned successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to assign grade']);
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
