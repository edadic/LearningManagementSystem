<?php
session_start();
require_once '../../db.php';

// Check authentication - allow any logged in user to see available courses
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $database = connectToMongoDB();
    $coursesCollection = $database->courses;
    
    // Get all active courses
    $courses = $coursesCollection->find(['status' => 'active'])->toArray();
    
    // Format courses for response
    $formattedCourses = array_map(function($course) {
        return [
            '_id' => (string)$course['_id'],
            'course_name' => $course['course_name'] ?? $course['title'] ?? 'Unknown Course',
            'course_code' => $course['course_code'] ?? 'N/A',
            'description' => $course['description'] ?? '',
            'teacher_id' => $course['teacher_id'],
            'teacher_name' => $course['teacher_name'] ?? $course['instructor'] ?? 'Unknown Teacher',
            'created_at' => isset($course['created_at']) ? $course['created_at']->toDateTime()->format('Y-m-d H:i:s') : null
        ];
    }, $courses);
    
    echo json_encode($formattedCourses);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
