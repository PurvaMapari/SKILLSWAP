<?php
// ============================================================
//  config.php  —  SkillSwap Database Configuration
//  Place this whole project inside htdocs/SKILLSWAP/
//  Make sure XAMPP Apache + MySQL are running
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // default XAMPP user
define('DB_PASS', '');             // default XAMPP password (empty)
define('DB_NAME', 'skillswap_db');

// Create connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
        exit;
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// CORS + JSON headers for all API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
