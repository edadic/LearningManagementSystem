<?php
session_start();
require_once '../../db.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Parent') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $database = connectToMongoDB();
    $usersCollection = $database->users;
    $parentChildrenCollection = $database->parent_children;
    
    $method = $_SERVER['REQUEST_METHOD'];
    $parent_id = $_SESSION['user_id'];
    
    if ($method === 'GET') {
        // Get children associated with this parent
        $relationships = $parentChildrenCollection->find(['parent_id' => $parent_id]);
        $childrenIds = [];
        
        foreach ($relationships as $relationship) {
            // Handle both ObjectId and string formats for student_id
            $studentId = $relationship['student_id'];
            try {
                // Try to convert to ObjectId if it's a string
                if (is_string($studentId)) {
                    $childrenIds[] = new MongoDB\BSON\ObjectId($studentId);
                } else {
                    $childrenIds[] = $studentId;
                }
            } catch (Exception $e) {
                // If conversion fails, keep as string
                $childrenIds[] = $studentId;
            }
        }
        
        if (empty($childrenIds)) {
            echo json_encode(['children' => []]);
            exit();
        }
        
        $children = $usersCollection->find([
            '_id' => ['$in' => $childrenIds],
            'role' => 'Student'
        ]);
        
        $childrenList = [];
        
        foreach ($children as $child) {
            $childrenList[] = [
                'id' => (string)$child['_id'],
                'name' => $child['name'],
                'email' => $child['email'],
                'created_at' => isset($child['created_at']) ? $child['created_at']->toDateTime()->format('Y-m-d H:i:s') : null
            ];
        }
        
        echo json_encode(['children' => $childrenList]);
        
    } else if ($method === 'POST') {
        // Add a child (link student to parent)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['student_email'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Student email is required']);
            exit();
        }
        
        $student_email = $input['student_email'];
        
        // Find student by email
        $student = $usersCollection->findOne([
            'email' => $student_email,
            'role' => 'Student'
        ]);
        
        if (!$student) {
            http_response_code(404);
            echo json_encode(['error' => 'Student not found']);
            exit();
        }
        
        $student_id = (string)$student['_id'];
        
        // Check if relationship already exists
        $existingRelationship = $parentChildrenCollection->findOne([
            'parent_id' => $parent_id,
            'student_id' => $student_id
        ]);
        
        if ($existingRelationship) {
            http_response_code(409);
            echo json_encode(['error' => 'Child is already linked to this parent']);
            exit();
        }
        
        // Create parent-child relationship
        $relationship = [
            'parent_id' => $parent_id,
            'student_id' => $student_id,
            'linked_at' => new MongoDB\BSON\UTCDateTime(),
            'status' => 'active'
        ];
        
        $result = $parentChildrenCollection->insertOne($relationship);
        
        if ($result->getInsertedCount() > 0) {
            echo json_encode([
                'message' => 'Child linked successfully',
                'child' => [
                    'id' => $student_id,
                    'name' => $student['name'],
                    'email' => $student['email']
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to link child']);
        }
        
    } else if ($method === 'DELETE') {
        // Remove a child (unlink student from parent)
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['student_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Student ID is required']);
            exit();
        }
        
        $student_id = $input['student_id'];
        
        $result = $parentChildrenCollection->deleteOne([
            'parent_id' => $parent_id,
            'student_id' => $student_id
        ]);
        
        if ($result->getDeletedCount() > 0) {
            echo json_encode(['message' => 'Child unlinked successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Relationship not found']);
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
