<?php
// ============================================================
//  api/swap_request.php  —  Create / List swap requests
//  POST: Create a new swap request
//  GET : ?user_id=X  → fetch requests for that user
// ============================================================
require_once 'config.php';
require_once 'send_email.php';

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

    // ── DELETE REQUEST ──
    if ($action === 'delete_request') {
        $requestId = intval($data['request_id'] ?? 0);
        $userId    = intval($data['user_id']    ?? 0);

        if (!$requestId || !$userId) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
            exit;
        }

        // Only allow the sender to delete their own pending request
        $stmt = $conn->prepare("DELETE FROM swap_requests WHERE id = ? AND sender_id = ? AND status = 'pending'");
        $stmt->bind_param('ii', $requestId, $userId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Request deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Could not delete request.']);
        }
        $stmt->close();
        $conn->close();
        exit;
    }

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
            // Send email notification when a swap is accepted
            if ($status === 'accepted') {
                // Look up the swap request details + sender info
                $infoStmt = $conn->prepare("
                    SELECT sr.*, 
                           sender.name AS sender_name, sender.email AS sender_email,
                           accepter.name AS receiver_name
                    FROM swap_requests sr
                    JOIN users sender ON sr.sender_id = sender.id
                    LEFT JOIN users accepter ON sr.receiver_id = accepter.id
                    WHERE sr.id = ?
                ");
                $infoStmt->bind_param('i', $requestId);
                $infoStmt->execute();
                $info = $infoStmt->get_result()->fetch_assoc();
                $infoStmt->close();

                if ($info && $info['sender_email']) {
                    $accepterName = $info['receiver_name'] ?? 'A user';
                    $emailBody = '
                        <p style="color:#f5f0eb;font-size:16px;">Great news! <strong style="color:#f97316;">' . htmlspecialchars($accepterName) . '</strong> has accepted your swap request.</p>
                        <div style="background:#252525;border:1px solid #333;border-radius:12px;padding:18px;margin:20px 0;">
                            <p style="margin:0 0 8px;color:#a8a29e;font-size:13px;text-transform:uppercase;letter-spacing:0.05em;">Swap Details</p>
                            <p style="margin:0;color:#f5f0eb;"><strong>Your Skill:</strong> ' . htmlspecialchars($info['skill_offered']) . '</p>
                            <p style="margin:4px 0 0;color:#f5f0eb;"><strong>In Exchange For:</strong> ' . htmlspecialchars($info['skill_wanted']) . '</p>
                        </div>
                        <p style="color:#a8a29e;">You can now start messaging to coordinate your swap sessions. Head over to your dashboard to begin!</p>
                    ';
                    $html = buildEmailTemplate('Your Swap Request Was Accepted! 🎉', $emailBody);
                    sendNotificationEmail($info['sender_email'], 'SkillSwap — Your swap request was accepted!', $html);
                }
            }

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

    // Send email notification to the receiver (if specified)
    if ($receiverId > 0) {
        // Look up receiver email and sender name
        $lookupStmt = $conn->prepare("
            SELECT r.email AS receiver_email, r.name AS receiver_name, s.name AS sender_name
            FROM users r, users s
            WHERE r.id = ? AND s.id = ?
        ");
        $lookupStmt->bind_param('ii', $receiverId, $senderId);
        $lookupStmt->execute();
        $lookupResult = $lookupStmt->get_result()->fetch_assoc();
        $lookupStmt->close();

        if ($lookupResult && $lookupResult['receiver_email']) {
            $senderName = $lookupResult['sender_name'] ?? 'Someone';
            $emailBody = '
                <p style="color:#f5f0eb;font-size:16px;"><strong style="color:#f97316;">' . htmlspecialchars($senderName) . '</strong> has sent you a swap request!</p>
                <div style="background:#252525;border:1px solid #333;border-radius:12px;padding:18px;margin:20px 0;">
                    <p style="margin:0 0 8px;color:#a8a29e;font-size:13px;text-transform:uppercase;letter-spacing:0.05em;">Swap Details</p>
                    <p style="margin:0;color:#f5f0eb;"><strong>They Offer:</strong> ' . htmlspecialchars($skillOffered) . '</p>
                    <p style="margin:4px 0 0;color:#f5f0eb;"><strong>They Want:</strong> ' . htmlspecialchars($skillWanted) . '</p>
                    ' . ($message ? '<p style="margin:12px 0 0;color:#a8a29e;font-style:italic;">"' . htmlspecialchars($message) . '"</p>' : '') . '
                </div>
                <p style="color:#a8a29e;">Head over to your dashboard to accept or decline the request.</p>
            ';
            $html = buildEmailTemplate('New Swap Request! 🔥', $emailBody);
            sendNotificationEmail($lookupResult['receiver_email'], 'SkillSwap — You have a new swap request!', $html);
        }
    }

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
               u.name AS sender_name, u.phone AS sender_phone,
               r.name AS receiver_name, r.phone AS receiver_phone
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
