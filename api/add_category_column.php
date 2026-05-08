<?php
// One-time migration: Add category column to swap_requests
require_once 'config.php';

$conn = getConnection();

$result = $conn->query("SHOW COLUMNS FROM swap_requests LIKE 'category'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE swap_requests ADD COLUMN category VARCHAR(80) DEFAULT NULL AFTER skill_wanted");
    echo json_encode(['success' => true, 'message' => 'category column added to swap_requests']);
} else {
    echo json_encode(['success' => true, 'message' => 'category column already exists']);
}

$conn->close();
