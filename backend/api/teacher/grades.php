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
$gradesCollection = $database->grades;
$assignmentsCollection = $database->assignments;
$usersCollection = $database->users;

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get grades for teacher's assignments
        if (isset($_GET['assignment_id'])) {
            // Get grades for specific assignment
            $assignment = $assignmentsCollection->findOne([
                '_id' => new MongoDB\BSON\ObjectId($_GET['assignment_id']),
                'teacher_id' => $_SESSION['user_id']
            ]);
            
            if (!$assignment) {
                http_response_code(403);
                echo json_encode(['error' => 'Access denied']);
                exit();
            }
            
            $grades = $gradesCollection->find(['assignment_id' => $_GET['assignment_id']])->toArray();
            echo json_encode($grades);
        } else {
            // Get all grades for teacher's assignments
            $teacherAssignments = $assignmentsCollection->find(['teacher_id' => $_SESSION['user_id']])->toArray();
            $assignmentIds = array_map(function($assignment) {
                return (string)$assignment['_id'];
            }, $teacherAssignments);
            
            $grades = $gradesCollection->find(['assignment_id' => ['$in' => $assignmentIds]])->toArray();
            echo json_encode($grades);
        }
        break;
        
    case 'POST':
        // Assign grade
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['assignment_id']) || !isset($input['student_id']) || !isset($input['points'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit();
        }
        
        // Verify assignment belongs to this teacher
        $assignment = $assignmentsCollection->findOne([
            '_id' => new MongoDB\BSON\ObjectId($input['assignment_id']),
            'teacher_id' => $_SESSION['user_id']
        ]);
        
        if (!$assignment) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied to this assignment']);
            exit();
        }
        
        // Get student info
        $student = $usersCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($input['student_id'])]);
        
        if (!$student) {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
            exit();
        }
        
        // Check if grade already exists
        $existingGrade = $gradesCollection->findOne([
            'assignment_id' => $input['assignment_id'],
            'student_id' => $input['student_id']
        ]);
        
        $gradeDocument = [
            'assignment_id' => $input['assignment_id'],
            'assignment_title' => $assignment['title'],
            'course_id' => $assignment['course_id'],
            'course_name' => $assignment['course_name'],
            'student_id' => $input['student_id'],
            'student_name' => $student['name'],
            'teacher_id' => $_SESSION['user_id'],
            'teacher_name' => $_SESSION['name'],
            'points' => (float)$input['points'],
            'max_points' => $assignment['max_points'],
            'percentage' => round(((float)$input['points'] / $assignment['max_points']) * 100, 2),
            'feedback' => $input['feedback'] ?? '',
            'graded_at' => new MongoDB\BSON\UTCDateTime(),
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        if ($existingGrade) {
            // Update existing grade
            $result = $gradesCollection->updateOne(
                ['_id' => $existingGrade['_id']],
                ['$set' => $gradeDocument]
            );
            echo json_encode(['success' => $result->getModifiedCount() > 0]);
        } else {
            // Create new grade
            $result = $gradesCollection->insertOne($gradeDocument);
            echo json_encode(['success' => $result->getInsertedCount() > 0, 'grade_id' => (string)$result->getInsertedId()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
