<?php
require_once 'vendor/autoload.php';

try {
    // Create MongoDB connection
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->LMS;
    
    // Collections
    $usersCollection = $database->users;
    $coursesCollection = $database->courses;
    $assignmentsCollection = $database->assignments;
    $enrollmentsCollection = $database->enrollments;
    $parentChildrenCollection = $database->parent_children;
    $submissionsCollection = $database->submissions;
    
    echo "Setting up test data...\n";
    
    // Clear existing data
    $usersCollection->deleteMany([]);
    $coursesCollection->deleteMany([]);
    $assignmentsCollection->deleteMany([]);
    $enrollmentsCollection->deleteMany([]);
    $parentChildrenCollection->deleteMany([]);
    $submissionsCollection->deleteMany([]);
    
    echo "Cleared existing data.\n";
    
    // Create test users
    $users = [
        [
            'name' => 'John Teacher',
            'email' => 'teacher@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'Teacher',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Alice Student',
            'email' => 'student@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'Student',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Bob Student',
            'email' => 'student2@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'Student',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'name' => 'Mary Parent',
            'email' => 'parent@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'Parent',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];
    
    $userResult = $usersCollection->insertMany($users);
    $userIds = $userResult->getInsertedIds();
    echo "Created " . count($userIds) . " test users.\n";
    
    // Get user IDs for relationships
    $teacher = $usersCollection->findOne(['email' => 'teacher@example.com']);
    $student1 = $usersCollection->findOne(['email' => 'student@example.com']);
    $student2 = $usersCollection->findOne(['email' => 'student2@example.com']);
    $parent = $usersCollection->findOne(['email' => 'parent@example.com']);
    
    // Create test courses
    $courses = [
        [
            'title' => 'Mathematics 101',
            'description' => 'Introduction to Algebra and Basic Mathematics',
            'instructor' => 'John Teacher',
            'teacher_id' => (string)$teacher['_id'],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'title' => 'English Literature',
            'description' => 'Classic and Modern Literature Analysis',
            'instructor' => 'John Teacher',
            'teacher_id' => (string)$teacher['_id'],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ],
        [
            'title' => 'Science Fundamentals',
            'description' => 'Basic Physics, Chemistry, and Biology',
            'instructor' => 'John Teacher',
            'teacher_id' => (string)$teacher['_id'],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];
    
    $courseResult = $coursesCollection->insertMany($courses);
    $courseIds = $courseResult->getInsertedIds();
    echo "Created " . count($courseIds) . " test courses.\n";
    
    // Create enrollments
    $enrollments = [];
    foreach ($courseIds as $courseId) {
        $enrollments[] = [
            'student_id' => (string)$student1['_id'],
            'course_id' => (string)$courseId,
            'enrolled_at' => new MongoDB\BSON\UTCDateTime(),
            'status' => 'active'
        ];
        $enrollments[] = [
            'student_id' => (string)$student2['_id'],
            'course_id' => (string)$courseId,
            'enrolled_at' => new MongoDB\BSON\UTCDateTime(),
            'status' => 'active'
        ];
    }
    
    $enrollmentResult = $enrollmentsCollection->insertMany($enrollments);
    echo "Created " . count($enrollments) . " enrollments.\n";
    
    // Create parent-child relationship
    $parentChildrenCollection->insertOne([
        'parent_id' => (string)$parent['_id'],
        'student_id' => (string)$student1['_id'],
        'linked_at' => new MongoDB\BSON\UTCDateTime(),
        'status' => 'active'
    ]);
    echo "Created parent-child relationship.\n";
    
    // Create test assignments
    $assignments = [];
    $courseDocs = $coursesCollection->find();
    foreach ($courseDocs as $course) {
        $assignments[] = [
            'title' => 'Assignment 1: ' . $course['title'],
            'description' => 'Complete the first assignment for ' . $course['title'],
            'course_id' => (string)$course['_id'],
            'teacher_id' => $course['teacher_id'],
            'due_date' => new MongoDB\BSON\UTCDateTime(strtotime('+1 week') * 1000),
            'max_points' => 100,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        $assignments[] = [
            'title' => 'Assignment 2: ' . $course['title'],
            'description' => 'Complete the second assignment for ' . $course['title'],
            'course_id' => (string)$course['_id'],
            'teacher_id' => $course['teacher_id'],
            'due_date' => new MongoDB\BSON\UTCDateTime(strtotime('+2 weeks') * 1000),
            'max_points' => 100,
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
    }
    
    $assignmentResult = $assignmentsCollection->insertMany($assignments);
    $assignmentIds = $assignmentResult->getInsertedIds();
    echo "Created " . count($assignmentIds) . " assignments.\n";
    
    // Create some test submissions and grades
    $submissions = [];
    $assignmentDocs = $assignmentsCollection->find();
    $submissionCount = 0;
    
    foreach ($assignmentDocs as $assignment) {
        // Student 1 submissions
        if ($submissionCount % 2 == 0) { // Submit every other assignment
            $grade = rand(70, 100);
            $submissions[] = [
                'assignment_id' => (string)$assignment['_id'],
                'student_id' => (string)$student1['_id'],
                'content' => 'This is my submission for ' . $assignment['title'],
                'submitted_at' => new MongoDB\BSON\UTCDateTime(strtotime('-2 days') * 1000),
                'status' => 'submitted',
                'grade' => $grade,
                'feedback' => 'Good work! Grade: ' . $grade . '/100',
                'graded_at' => new MongoDB\BSON\UTCDateTime(strtotime('-1 day') * 1000)
            ];
        }
        
        // Student 2 submissions
        if ($submissionCount % 3 == 0) { // Submit every third assignment
            $grade = rand(60, 95);
            $submissions[] = [
                'assignment_id' => (string)$assignment['_id'],
                'student_id' => (string)$student2['_id'],
                'content' => 'Here is my assignment submission for ' . $assignment['title'],
                'submitted_at' => new MongoDB\BSON\UTCDateTime(strtotime('-3 days') * 1000),
                'status' => 'submitted',
                'grade' => $grade,
                'feedback' => 'Nice effort! Grade: ' . $grade . '/100',
                'graded_at' => new MongoDB\BSON\UTCDateTime(strtotime('-1 day') * 1000)
            ];
        }
        
        $submissionCount++;
    }
    
    if (!empty($submissions)) {
        $submissionResult = $submissionsCollection->insertMany($submissions);
        echo "Created " . count($submissions) . " submissions with grades.\n";
    }
    
    echo "\nTest data setup completed successfully!\n";
    echo "\nTest credentials:\n";
    echo "Teacher: teacher@example.com / password123\n";
    echo "Student 1: student@example.com / password123\n";
    echo "Student 2: student2@example.com / password123\n";
    echo "Parent: parent@example.com / password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
