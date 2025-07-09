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
$coursesCollection = $database->courses;

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get all courses for this teacher
        $courses = $coursesCollection->find(['teacher_id' => $_SESSION['user_id']])->toArray();
        
        // Convert MongoDB objects to proper format for JSON
        $formattedCourses = array_map(function($course) {
            $formatted = [
                '_id' => (string)$course['_id'],
                'course_name' => $course['course_name'] ?? $course['title'] ?? 'Unknown Course',
                'course_code' => $course['course_code'] ?? 'N/A',
                'description' => $course['description'] ?? '',
                'teacher_id' => $course['teacher_id'],
                'teacher_name' => $course['teacher_name'] ?? $course['instructor'] ?? 'Unknown Teacher',
                'students' => $course['students'] ?? [],
                'status' => $course['status'] ?? 'active'
            ];
            
            // Handle created_at and updated_at
            if (isset($course['created_at'])) {
                $formatted['created_at'] = $course['created_at']->toDateTime()->format('Y-m-d H:i:s');
            }
            if (isset($course['updated_at'])) {
                $formatted['updated_at'] = $course['updated_at']->toDateTime()->format('Y-m-d H:i:s');
            }
            
            return $formatted;
        }, $courses);
        
        echo json_encode($formattedCourses);
        break;
        
    case 'POST':
        // Create new course
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['course_name']) || !isset($input['course_code'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit();
        }
        
        $courseDocument = [
            'course_name' => $input['course_name'],
            'course_code' => $input['course_code'],
            'description' => $input['description'] ?? '',
            'teacher_id' => $_SESSION['user_id'],
            'teacher_name' => $_SESSION['name'],
            'students' => [],
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime(),
            'status' => 'active'
        ];
        
        $result = $coursesCollection->insertOne($courseDocument);
        
        if ($result->getInsertedCount() > 0) {
            echo json_encode(['success' => true, 'course_id' => (string)$result->getInsertedId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create course']);
        }
        break;
        
    case 'PUT':
        // Update course
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['course_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing course ID']);
            exit();
        }
        
        // Validate ObjectId format
        if (!preg_match('/^[a-f\d]{24}$/i', $input['course_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid course ID format']);
            exit();
        }
        
        $updateData = [
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        if (isset($input['course_name'])) $updateData['course_name'] = $input['course_name'];
        if (isset($input['description'])) $updateData['description'] = $input['description'];
        if (isset($input['status'])) $updateData['status'] = $input['status'];
        
        try {
            $result = $coursesCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($input['course_id']), 'teacher_id' => $_SESSION['user_id']],
                ['$set' => $updateData]
            );
            
            echo json_encode(['success' => $result->getModifiedCount() > 0]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid course ID: ' . $e->getMessage()]);
        }
        break;
        
    case 'DELETE':
        // Delete course
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['course_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing course ID']);
            exit();
        }
        
        // Validate ObjectId format
        if (!preg_match('/^[a-f\d]{24}$/i', $input['course_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid course ID format']);
            exit();
        }
        
        try {
            $result = $coursesCollection->deleteOne([
                '_id' => new MongoDB\BSON\ObjectId($input['course_id']),
                'teacher_id' => $_SESSION['user_id']
            ]);
            
            echo json_encode(['success' => $result->getDeletedCount() > 0]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid course ID: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
