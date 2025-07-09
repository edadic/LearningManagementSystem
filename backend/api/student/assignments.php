<?php
session_start();
require_once '../../db.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $database = connectToMongoDB();
    $assignmentsCollection = $database->assignments;
    $enrollmentsCollection = $database->enrollments;
    $coursesCollection = $database->courses;
    $submissionsCollection = $database->submissions;
    
    $method = $_SERVER['REQUEST_METHOD'];
    $student_id = $_SESSION['user_id'];
    
    if ($method === 'GET') {
        // Get assignments for enrolled courses
        $enrollments = $enrollmentsCollection->find(['student_id' => $student_id]);
        $courseIds = [];
        
        foreach ($enrollments as $enrollment) {
            $courseIds[] = $enrollment['course_id'];
        }
        
        if (empty($courseIds)) {
            echo json_encode(['assignments' => []]);
            exit();
        }

        // Convert course IDs to ObjectIds for assignment query
        $objectIds = array_map(function($id) {
            try {
                return new MongoDB\BSON\ObjectId($id);
            } catch (Exception $e) {
                return $id; // Keep as string if it's not a valid ObjectId
            }
        }, $courseIds);

        $assignments = $assignmentsCollection->find(['course_id' => ['$in' => $courseIds]]);
        $assignmentList = [];
        
        foreach ($assignments as $assignment) {
            // Check if student has submitted this assignment
            $submission = $submissionsCollection->findOne([
                'assignment_id' => (string)$assignment['_id'],
                'student_id' => $student_id
            ]);
            
            // Get course name - try both ObjectId and string formats
            $course = null;
            try {
                $course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($assignment['course_id'])]);
            } catch (Exception $e) {
                // If ObjectId conversion fails, the course_id might be stored as string
                $course = $coursesCollection->findOne(['_id' => $assignment['course_id']]);
            }
            
            $assignmentList[] = [
                'id' => (string)$assignment['_id'],
                'title' => $assignment['title'],
                'description' => $assignment['description'] ?? '',
                'course_id' => $assignment['course_id'],
                'course_title' => $course ? ($course['course_name'] ?? $course['title'] ?? 'Unknown Course') : 'Unknown Course',
                'due_date' => isset($assignment['due_date']) ? $assignment['due_date']->toDateTime()->format('Y-m-d') : null,
                'max_points' => $assignment['max_points'] ?? 100,
                'created_at' => isset($assignment['created_at']) ? $assignment['created_at']->toDateTime()->format('Y-m-d H:i:s') : null,
                'submitted' => $submission !== null,
                'submission_date' => $submission ? $submission['submitted_at']->toDateTime()->format('Y-m-d H:i:s') : null,
                'grade' => $submission['grade'] ?? null
            ];
        }
        
        // Sort by due date
        usort($assignmentList, function($a, $b) {
            if (!$a['due_date'] && !$b['due_date']) return 0;
            if (!$a['due_date']) return 1;
            if (!$b['due_date']) return -1;
            return strtotime($a['due_date']) - strtotime($b['due_date']);
        });
        
        echo json_encode(['assignments' => $assignmentList]);
        
    } else if ($method === 'POST') {
        // Submit assignment
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['assignment_id']) || !isset($input['content'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Assignment ID and content are required']);
            exit();
        }
        
        $assignment_id = $input['assignment_id'];
        $content = $input['content'];
        
        // Check if assignment exists and student is enrolled in the course
        $assignment = $assignmentsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($assignment_id)]);
        if (!$assignment) {
            http_response_code(404);
            echo json_encode(['error' => 'Assignment not found']);
            exit();
        }
        
        $enrollment = $enrollmentsCollection->findOne([
            'student_id' => $student_id,
            'course_id' => $assignment['course_id']
        ]);
        
        if (!$enrollment) {
            http_response_code(403);
            echo json_encode(['error' => 'Not enrolled in this course']);
            exit();
        }
        
        // Check if already submitted
        $existingSubmission = $submissionsCollection->findOne([
            'assignment_id' => $assignment_id,
            'student_id' => $student_id
        ]);
        
        if ($existingSubmission) {
            // Update submission
            $result = $submissionsCollection->updateOne(
                ['_id' => $existingSubmission['_id']],
                ['$set' => [
                    'content' => $content,
                    'submitted_at' => new MongoDB\BSON\UTCDateTime(),
                    'status' => 'submitted'
                ]]
            );
            
            if ($result->getModifiedCount() > 0) {
                echo json_encode(['message' => 'Assignment submission updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update submission']);
            }
        } else {
            // Create new submission
            $submission = [
                'assignment_id' => $assignment_id,
                'student_id' => $student_id,
                'content' => $content,
                'submitted_at' => new MongoDB\BSON\UTCDateTime(),
                'status' => 'submitted'
            ];
            
            $result = $submissionsCollection->insertOne($submission);
            
            if ($result->getInsertedCount() > 0) {
                echo json_encode(['message' => 'Assignment submitted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to submit assignment']);
            }
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
