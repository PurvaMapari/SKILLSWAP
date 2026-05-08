<?php
// ============================================================
//  api/swap_request.php  —  Create / List swap requests
//  POST: Create a new swap request
//  GET : ?user_id=X  → fetch requests for that user
// ============================================================
require_once 'config.php';

$conn = getConnection();

// ── POST — create swap request ───────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $senderId     = intval($data['sender_id']     ?? 0);
    $receiverId   = intval($data['receiver_id']   ?? 0);
  
    $skillOffered = trim($data['skill_offered']   ?? '');
    $skillWanted  = trim($data['skill_wanted']    ?? '');
    $category     = trim($data['category']        ?? '');
    $skillLevel   = trim($data['skill_level']     ?? '');
    $message      = trim($data['message']         ?? '');
    $action       = trim($data['action']          ?? '');

    // ── UPDATE STATUS ──
    if ($action === 'update_status') {
        $requestId = intval($data['request_id'] ?? 0);
        $status    = trim($data['status']       ?? ''); // 'accepted' or 'declined'
        
        if (!$requestId || !in_array($status, ['accepted', 'declined'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters for status update.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE swap_requests SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $requestId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update status.']);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

    // ── CREATE NEW REQUEST ──
    if (!$senderId || !$skillOffered || !$skillWanted) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
   

  if ($receiverId > 0) {
    $stmt = $conn->prepare("
      INSERT INTO swap_requests (sender_id, receiver_id, skill_offered, skill_wanted, category, skill_level, message)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('iisssss', $senderId, $receiverId, $skillOffered, $skillWanted, $category, $skillLevel, $message);
  } else {
    $stmt = $conn->prepare("
      INSERT INTO swap_requests (sender_id, receiver_id, skill_offered, skill_wanted, category, skill_level, message)
      VALUES (?, NULL, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('isssss', $senderId, $skillOffered, $skillWanted, $category, $skillLevel, $message);
  }

if ($stmt->execute()) {

    echo json_encode([
        'success' => true,
        'message' => 'Swap request sent successfully!'
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => $stmt->error
    ]);
}
    exit;
}

// ── GET — fetch single request or list ──────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Single request by ID (for detail page)
    if (isset($_GET['id'])) {
        $reqId = intval($_GET['id']);
        $stmt = $conn->prepare("
            SELECT sr.*, u.name AS sender_name, u.email AS sender_email, u.phone AS sender_phone,
                   u.bio AS sender_bio, u.location AS sender_location
            FROM swap_requests sr
            JOIN users u ON sr.sender_id = u.id
            WHERE sr.id = ?
        ");
        $stmt->bind_param('i', $reqId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        if ($row) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Request not found.']);
        }
        exit;
    }

    // List all requests (dashboard)
    $result = $conn->query("
        SELECT sr.*, 
               u.name AS sender_name,
               r.name AS receiver_name
        FROM swap_requests sr
        JOIN users u ON sr.sender_id = u.id
        LEFT JOIN users r ON sr.receiver_id = r.id
        ORDER BY sr.created_at DESC
    ");

    $requests = [];

    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $requests
    ]);

    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
