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
    $coursesCollection = $database->courses;
    $enrollmentsCollection = $database->enrollments;
    
    $method = $_SERVER['REQUEST_METHOD'];
    $student_id = $_SESSION['user_id'];
    
    if ($method === 'GET') {
        // Get enrolled courses for the student
        $enrollments = $enrollmentsCollection->find(['student_id' => $student_id]);
        $courseIds = [];
        
        foreach ($enrollments as $enrollment) {
            $courseIds[] = $enrollment['course_id'];
        }
        
        if (empty($courseIds)) {
            echo json_encode(['courses' => []]);
            exit();
        }
        
        // Convert course IDs to ObjectIds for MongoDB query
        $objectIds = array_map(function($id) {
            try {
                return new MongoDB\BSON\ObjectId($id);
            } catch (Exception $e) {
                return null;
            }
        }, $courseIds);
        
        // Filter out null values
        $objectIds = array_filter($objectIds);
        
        if (empty($objectIds)) {
            echo json_encode(['courses' => []]);
            exit();
        }
        
        $courses = $coursesCollection->find(['_id' => ['$in' => $objectIds]]);
        $courseList = [];
        
        foreach ($courses as $course) {
            $courseList[] = [
                'id' => (string)$course['_id'],
                'title' => $course['course_name'] ?? $course['title'] ?? 'Unknown Course',
                'description' => $course['description'] ?? '',
                'instructor' => $course['teacher_name'] ?? $course['instructor'] ?? 'Unknown Teacher',
                'created_at' => isset($course['created_at']) ? $course['created_at']->toDateTime()->format('Y-m-d H:i:s') : null
            ];
        }
        
        echo json_encode(['courses' => $courseList]);
        
    } else if ($method === 'POST') {
        // Enroll in a course
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['course_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Course ID is required']);
            exit();
        }
        
        $course_id = $input['course_id'];
        $course = null;
        
        // Check if course_id is a valid ObjectId format (24 characters hex)
        if (preg_match('/^[a-f\d]{24}$/i', $course_id)) {
            // Try to find by ObjectId
            try {
                $course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($course_id)]);
            } catch (Exception $e) {
                // Invalid ObjectId, continue to try course code
            }
        }
        
        // If not found by ObjectId, try to find by course code
        if (!$course) {
            $course = $coursesCollection->findOne(['course_code' => $course_id]);
            if ($course) {
                // Update course_id to use the actual ObjectId for enrollment
                $course_id = (string)$course['_id'];
            }
        }
        
        if (!$course) {
            http_response_code(404);
            echo json_encode(['error' => 'Course not found. Please check the course ID or course code.']);
            exit();
        }
        
        // Check if already enrolled
        $existingEnrollment = $enrollmentsCollection->findOne([
            'student_id' => $student_id,
            'course_id' => $course_id
        ]);
        
        if ($existingEnrollment) {
            http_response_code(409);
            echo json_encode(['error' => 'Already enrolled in this course']);
            exit();
        }
        
        // Create enrollment
        $enrollment = [
            'student_id' => $student_id,
            'course_id' => $course_id,
            'enrolled_at' => new MongoDB\BSON\UTCDateTime(),
            'status' => 'active'
        ];
        
        $result = $enrollmentsCollection->insertOne($enrollment);
        
        if ($result->getInsertedCount() > 0) {
            echo json_encode([
                'message' => 'Successfully enrolled in course',
                'course_name' => $course['course_name'] ?? $course['title'] ?? 'Unknown Course',
                'course_code' => $course['course_code'] ?? 'N/A'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to enroll in course']);
        }
        
    } else if ($method === 'DELETE') {
        // Unenroll from a course
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['course_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Course ID is required']);
            exit();
        }
        
        $course_id = $input['course_id'];
        
        $result = $enrollmentsCollection->deleteOne([
            'student_id' => $student_id,
            'course_id' => $course_id
        ]);
        
        if ($result->getDeletedCount() > 0) {
            echo json_encode(['message' => 'Successfully unenrolled from course']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Enrollment not found']);
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
