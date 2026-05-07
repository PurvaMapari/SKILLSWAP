<?php
// ============================================================
//  api/reviews.php  —  Get / Add user reviews
//  GET : ?user_id=X          → returns reviews for this user
//  POST: { user_id, reviewer_name, rating, review_text }
// ============================================================
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'user_id is required.']);
        exit;
    }
    
    $userId = intval($_GET['user_id']);
    $conn = getConnection();
    
    $stmt = $conn->prepare("SELECT * FROM reviews WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => true, 'data' => $reviews]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    
    $userId = intval($data['user_id'] ?? 0);
    $reviewerName = trim($data['reviewer_name'] ?? '');
    $rating = intval($data['rating'] ?? 5);
    $reviewText = trim($data['review_text'] ?? '');
    
    if (!$userId || !$reviewerName || !$reviewText) {
        echo json_encode(['success' => false, 'message' => 'user_id, reviewer_name, and review_text are required.']);
        exit;
    }
    
    if ($rating < 1 || $rating > 5) $rating = 5;
    
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, reviewer_name, rating, review_text) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isis', $userId, $reviewerName, $rating, $reviewText);
    
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        
        // Fetch the newly created review to return it
        $fetchStmt = $conn->prepare("SELECT * FROM reviews WHERE id = ?");
        $fetchStmt->bind_param('i', $id);
        $fetchStmt->execute();
        $newReview = $fetchStmt->get_result()->fetch_assoc();
        $fetchStmt->close();
        
        echo json_encode(['success' => true, 'message' => 'Review added successfully.', 'review' => $newReview]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add review.']);
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
