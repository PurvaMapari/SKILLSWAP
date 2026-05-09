<?php
// ============================================================
//  api/profile.php  —  Get / Update user profile
//  GET : ?user_id=X          → { bio, location }
//  POST: { user_id, bio, location }
// ============================================================
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'user_id is required.']);
        exit;
    }
    
    $userId = intval($_GET['user_id']);
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT email, bio, location, phone FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => [
            'email' => $row['email'] ? $row['email'] : '',
            'bio' => $row['bio'] ? $row['bio'] : '',
            'location' => $row['location'] ? $row['location'] : '',
            'phone' => $row['phone'] ? $row['phone'] : ''
        ]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    
    $userId = intval($data['user_id'] ?? 0);
    $bio = trim($data['bio'] ?? '');
    $location = trim($data['location'] ?? '');
    $phone = trim($data['phone'] ?? '');
    
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'user_id is required.']);
        exit;
    }
    
    $conn = getConnection();
    $stmt = $conn->prepare("UPDATE users SET bio = ?, location = ?, phone = ? WHERE id = ?");
    $stmt->bind_param('sssi', $bio, $location, $phone, $userId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
