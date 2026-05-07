<?php
// ============================================================
//  api/messages.php  —  Send / Fetch messages
//  POST: { sender_id, receiver_id, content }
//  GET : ?user_id=X   → all conversations for that user
//  GET : ?sender_id=X&receiver_id=Y  → thread between two users
// ============================================================
require_once 'config.php';

$conn = getConnection();

// ── POST — send message ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data       = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $senderId   = intval($data['sender_id']   ?? 0);
    $receiverId = intval($data['receiver_id'] ?? 0);
    $content    = trim($data['content']       ?? '');

    if (!$senderId || !$receiverId || !$content) {
        echo json_encode(['success' => false, 'message' => 'sender_id, receiver_id, and content are required.']);
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)"
    );
    $stmt->bind_param('iis', $senderId, $receiverId, $content);
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'message' => 'Message sent.']);
    } else {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
    }
    exit;
}

// ── GET ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Thread between two users
    if (isset($_GET['sender_id']) && isset($_GET['receiver_id'])) {
        $sid = intval($_GET['sender_id']);
        $rid = intval($_GET['receiver_id']);
        $stmt = $conn->prepare(
            "SELECT m.*, u.name AS sender_name
             FROM messages m
             JOIN users u ON m.sender_id = u.id
             WHERE (m.sender_id = ? AND m.receiver_id = ?)
                OR (m.sender_id = ? AND m.receiver_id = ?)
             ORDER BY m.created_at ASC"
        );
        $stmt->bind_param('iiii', $sid, $rid, $rid, $sid);
        $stmt->execute();
        $result = $stmt->get_result();
        $msgs = [];
        while ($row = $result->fetch_assoc()) $msgs[] = $row;
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'data' => $msgs]);
        exit;
    }

    // All conversations for a user
    if (isset($_GET['user_id'])) {
        $uid = intval($_GET['user_id']);
        $stmt = $conn->prepare(
            "SELECT m.*, 
                    us.name AS sender_name, 
                    ur.name AS receiver_name
             FROM messages m
             JOIN users us ON m.sender_id   = us.id
             JOIN users ur ON m.receiver_id = ur.id
             WHERE m.sender_id = ? OR m.receiver_id = ?
             ORDER BY m.created_at DESC"
        );
        $stmt->bind_param('ii', $uid, $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        $msgs = [];
        while ($row = $result->fetch_assoc()) $msgs[] = $row;
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'data' => $msgs]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
