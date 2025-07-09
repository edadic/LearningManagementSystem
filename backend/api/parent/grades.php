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
    $parentChildrenCollection = $database->parent_children;
    $submissionsCollection = $database->submissions;
    $assignmentsCollection = $database->assignments;
    $coursesCollection = $database->courses;
    
    $method = $_SERVER['REQUEST_METHOD'];
    $parent_id = $_SESSION['user_id'];
    
    if ($method === 'GET') {
        $child_id = $_GET['child_id'] ?? null;
        
        if (!$child_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Child ID is required']);
            exit();
        }
        
        // Verify parent-child relationship
        $relationship = $parentChildrenCollection->findOne([
            'parent_id' => $parent_id,
            'student_id' => $child_id
        ]);
        
        if (!$relationship) {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied to this child\'s grades']);
            exit();
        }
        
        // Get all grades for the child
        $submissions = $submissionsCollection->find([
            'student_id' => $child_id,
            'grade' => ['$exists' => true]
        ]);
        
        $gradeList = [];
        
        foreach ($submissions as $submission) {
            // Get assignment details
            $assignment = null;
            try {
                $assignment = $assignmentsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($submission['assignment_id'])]);
            } catch (Exception $e) {
                $assignment = $assignmentsCollection->findOne(['_id' => $submission['assignment_id']]);
            }
            if (!$assignment) continue;
            // Get course details
            $course = null;
            try {
                $course = $coursesCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($assignment['course_id'])]);
            } catch (Exception $e) {
                $course = $coursesCollection->findOne(['_id' => $assignment['course_id']]);
            }
            if (!$course) continue;
            $gradeList[] = [
                'assignment_id' => $submission['assignment_id'],
                'assignment_title' => $assignment['title'],
                'course_id' => $assignment['course_id'],
                'course_title' => $course['title'] ?? $course['course_name'] ?? 'Unknown Course',
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
