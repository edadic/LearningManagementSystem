<?php
require_once __DIR__ . '/../vendor/autoload.php';

function connectToMongoDB() {
    try {
        $client = new MongoDB\Client("mongodb://localhost:27017");
        return $client->LMS;
    } catch (Exception $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}
?>
