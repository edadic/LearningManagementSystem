<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignmentId = $_POST['assignmentId'] ?? null;
    $studentId = $_POST['studentId'] ?? null;
    $file = $_FILES['file'] ?? null;

    if (!$assignmentId || !$studentId || !$file) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $uploadDir = '../../uploads/assignments/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $submission = [
            'assignmentId' => $assignmentId,
            'studentId' => $studentId,
            'filePath' => $filePath,
            'uploadDate' => new MongoDB\BSON\UTCDateTime()
        ];

        $db = getDbConnection();
        $db->submissions->insertOne($submission);

        echo json_encode(['success' => true, 'submission' => $submission]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
