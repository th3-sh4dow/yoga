<?php
// Simple test file to check if PHP is working
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

echo json_encode([
    'success' => true,
    'message' => 'PHP is working correctly',
    'timestamp' => date('Y-m-d H:i:s'),
    'received_data' => $_POST
]);
?>