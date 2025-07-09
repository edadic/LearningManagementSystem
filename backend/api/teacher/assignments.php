<?php
session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';

// Check if user is logged in as teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$client = new MongoDB\Client("mongodb://localhost:27017");
$database = $client->LMS;
$assignmentsCollection = $database->assignments;
$coursesCollection = $database->courses;

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all assignments for this teacher's courses
        $teacherCourses = $coursesCollection->find(['teacher_id' => $_SESSION['user_id']])->toArray();
        $courseIds = array_map(function($course) {
            return (string)$course['_id'];
        }, $teacherCourses);
        
        $assignments = $assignmentsCollection->find(['course_id' => ['$in' => $courseIds]])->toArray();
        
        // Convert MongoDB objects to proper format for JSON
        $formattedAssignments = array_map(function($assignment) {
            $formatted = [
                '_id' => (string)$assignment['_id'],
                'title' => $assignment['title'],
                'description' => $assignment['description'] ?? '',
                'course_id' => $assignment['course_id'],
                'course_name' => $assignment['course_name'] ?? 'Unknown Course',
                'teacher_id' => $assignment['teacher_id'],
                'teacher_name' => $assignment['teacher_name'] ?? 'Unknown Teacher',
                'max_points' => $assignment['max_points'] ?? 100,
                'status' => $assignment['status'] ?? 'active',
                'submissions' => $assignment['submissions'] ?? []
            ];
            
            // Handle due_date conversion
            if (isset($assignment['due_date']) && $assignment['due_date']) {
                $formatted['due_date'] = $assignment['due_date']->toDateTime()->format('Y-m-d');
            } else {
                $formatted['due_date'] = null;
            }
            
            // Handle created_at and updated_at
            if (isset($assignment['created_at'])) {
                $formatted['created_at'] = $assignment['created_at']->toDateTime()->format('Y-m-d H:i:s');
            }
            if (isset($assignment['updated_at'])) {
                $formatted['updated_at'] = $assignment['updated_at']->toDateTime()->format('Y-m-d H:i:s');
            }
            
            return $formatted;
        }, $assignments);
        
        echo json_encode($formattedAssignments);
        break;
        
    case 'POST':
        // Create new assignment
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['title']) || !isset($input['course_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit();
        }
        
        // Verify the course belongs to this teacher
        $course = $coursesCollection->findOne([
            '_id' => new MongoDB\BSON\ObjectId($input['course_id']),
            'teacher_id' => $_SESSION['user_id']
        ]);
        
        if (!$course) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied to this course']);
            exit();
        }
        
        $assignmentDocument = [
            'title' => $input['title'],
            'description' => $input['description'] ?? '',
            'course_id' => $input['course_id'],
            'course_name' => $course['course_name'],
            'teacher_id' => $_SESSION['user_id'],
            'teacher_name' => $_SESSION['name'],
            'due_date' => isset($input['due_date']) ? new MongoDB\BSON\UTCDateTime(strtotime($input['due_date']) * 1000) : null,
            'max_points' => $input['max_points'] ?? 100,
            'status' => 'active',
            'submissions' => [],
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $assignmentsCollection->insertOne($assignmentDocument);
        
        if ($result->getInsertedCount() > 0) {
            echo json_encode(['success' => true, 'assignment_id' => (string)$result->getInsertedId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create assignment']);
        }
        break;
        
    case 'PUT':
        // Update assignment
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['assignment_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing assignment ID']);
            exit();
        }
        
        // Validate ObjectId format
        if (!preg_match('/^[a-f\d]{24}$/i', $input['assignment_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid assignment ID format']);
            exit();
        }
        
        $updateData = [
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        if (isset($input['title'])) $updateData['title'] = $input['title'];
        if (isset($input['description'])) $updateData['description'] = $input['description'];
        if (isset($input['due_date'])) $updateData['due_date'] = new MongoDB\BSON\UTCDateTime(strtotime($input['due_date']) * 1000);
        if (isset($input['max_points'])) $updateData['max_points'] = $input['max_points'];
        if (isset($input['status'])) $updateData['status'] = $input['status'];
        
        try {
            $result = $assignmentsCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($input['assignment_id']), 'teacher_id' => $_SESSION['user_id']],
                ['$set' => $updateData]
            );
            
            echo json_encode(['success' => $result->getModifiedCount() > 0]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid assignment ID: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Delete assignment
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['assignment_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing assignment ID']);
            exit();
        }
        
        // Validate ObjectId format
        if (!preg_match('/^[a-f\d]{24}$/i', $input['assignment_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid assignment ID format']);
            exit();
        }
        
        try {
            $result = $assignmentsCollection->deleteOne([
                '_id' => new MongoDB\BSON\ObjectId($input['assignment_id']),
                'teacher_id' => $_SESSION['user_id']
            ]);
            
            echo json_encode(['success' => $result->getDeletedCount() > 0]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid assignment ID: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
