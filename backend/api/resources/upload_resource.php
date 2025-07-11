<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = $_POST['courseId'] ?? null;
    $week = $_POST['week'] ?? null;
    $file = $_FILES['file'] ?? null;

    if (!$courseId || !$week || !$file) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $uploadDir = '../../uploads/resources/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $uploadDir . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        $resource = [
            'courseId' => $courseId,
            'week' => $week,
            'filePath' => $filePath,
            'uploadDate' => new MongoDB\BSON\UTCDateTime()
        ];

        $db = getDbConnection();
        $db->resources->insertOne($resource);

        echo json_encode(['success' => true, 'resource' => $resource]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
