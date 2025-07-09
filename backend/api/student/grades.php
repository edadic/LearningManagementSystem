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
    $submissionsCollection = $database->submissions;
    $assignmentsCollection = $database->assignments;
    $coursesCollection = $database->courses;
    $enrollmentsCollection = $database->enrollments;
    
    $method = $_SERVER['REQUEST_METHOD'];
    $student_id = $_SESSION['user_id'];
    
    if ($method === 'GET') {
        // Get all grades for the student
        $submissions = $submissionsCollection->find([
            'student_id' => $student_id,
            'grade' => ['$exists' => true]
        ]);
        
        $gradeList = [];
        
        foreach ($submissions as $submission) {
            // Get assignment details
            $assignment = $assignmentsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($submission['assignment_id'])]);
            if (!$assignment) continue;
            
            // Get course details
            $course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($assignment['course_id'])]);
            if (!$course) continue;
            
            // Verify student is enrolled in the course
            $enrollment = $enrollmentsCollection->findOne([
                'student_id' => $student_id,
                'course_id' => $assignment['course_id']
            ]);
            if (!$enrollment) continue;
            
            $gradeList[] = [
                'assignment_id' => $submission['assignment_id'],
                'assignment_title' => $assignment['title'],
                'course_id' => $assignment['course_id'],
                'course_title' => $course['title'],
                'grade' => $submission['grade'],
                'max_points' => $assignment['max_points'] ?? 100,
                'percentage' => round(($submission['grade'] / ($assignment['max_points'] ?? 100)) * 100, 2),
                'feedback' => $submission['feedback'] ?? null,
                'graded_at' => isset($submission['graded_at']) ? $submission['graded_at']->toDateTime()->format('Y-m-d H:i:s') : null,
                'submitted_at' => isset($submission['submitted_at']) ? $submission['submitted_at']->toDateTime()->format('Y-m-d H:i:s') : null
            ];
        }
        
        // Sort by graded date (most recent first)
        usort($gradeList, function($a, $b) {
            if (!$a['graded_at'] && !$b['graded_at']) return 0;
            if (!$a['graded_at']) return 1;
            if (!$b['graded_at']) return -1;
            return strtotime($b['graded_at']) - strtotime($a['graded_at']);
        });
        
        // Calculate overall statistics
        $totalGrades = count($gradeList);
        $totalPoints = array_sum(array_column($gradeList, 'grade'));
        $maxTotalPoints = array_sum(array_column($gradeList, 'max_points'));
        $overallPercentage = $maxTotalPoints > 0 ? round(($totalPoints / $maxTotalPoints) * 100, 2) : 0;
        
        // Calculate grade distribution by course
        $courseGrades = [];
        foreach ($gradeList as $grade) {
            $courseId = $grade['course_id'];
            if (!isset($courseGrades[$courseId])) {
                $courseGrades[$courseId] = [
                    'course_title' => $grade['course_title'],
                    'grades' => [],
                    'total_points' => 0,
                    'max_points' => 0
                ];
            }
            $courseGrades[$courseId]['grades'][] = $grade;
            $courseGrades[$courseId]['total_points'] += $grade['grade'];
            $courseGrades[$courseId]['max_points'] += $grade['max_points'];
        }
        
        // Calculate average for each course
        foreach ($courseGrades as $courseId => &$courseData) {
            $courseData['average'] = $courseData['max_points'] > 0 ? 
                round(($courseData['total_points'] / $courseData['max_points']) * 100, 2) : 0;
        }
        
        echo json_encode([
            'grades' => $gradeList,
            'statistics' => [
                'total_assignments' => $totalGrades,
                'total_points' => $totalPoints,
                'max_total_points' => $maxTotalPoints,
                'overall_percentage' => $overallPercentage,
                'course_averages' => $courseGrades
            ]
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
