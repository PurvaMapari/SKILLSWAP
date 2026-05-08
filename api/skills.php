<?php
// ============================================================
//  api/skills.php  —  Add / List / Delete skills
//  POST: Add a skill         { user_id, title, description, type, category, level }
//  GET : ?user_id=X          → skills for one user
//  GET : (no params)         → all skills (explore page)
//  POST: action=delete       { skill_id, user_id }
// ============================================================
require_once 'config.php';

$conn = getConnection();

// ── POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    // Delete action
    if (($data['action'] ?? '') === 'delete') {
        $skillId = intval($data['skill_id'] ?? 0);
        $userId  = intval($data['user_id']  ?? 0);
        if (!$skillId || !$userId) {
            echo json_encode(['success' => false, 'message' => 'skill_id and user_id required.']);
            exit;
        }
        $stmt = $conn->prepare("DELETE FROM skills WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ii', $skillId, $userId);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        $conn->close();
        if ($affected > 0)
            echo json_encode(['success' => true,  'message' => 'Skill deleted.']);
        else
            echo json_encode(['success' => false, 'message' => 'Skill not found or not yours.']);
        exit;
    }

    // Add skill
    $userId      = intval(trim($data['user_id']     ?? 0));
    $title       = trim($data['title']              ?? '');
    $description = trim($data['description']        ?? '');
    $type        = trim($data['type']               ?? 'offer');
    $category    = trim($data['category']           ?? '');
$skill_level =
trim($data['skill_level'] ?? 'Beginner');

    if (!$userId || !$title) {
        echo json_encode(['success' => false, 'message' => 'user_id and title are required.']);
        exit;
    }
    $allowedTypes  = ['offer', 'want'];
    $allowedLevels = ['beginner', 'intermediate', 'advanced'];
    
    if (!in_array($type, $allowedTypes)) $type = 'offer';
    
    // Convert skill_level to lowercase for database enum
    $skill_level_lower = strtolower($skill_level);
    if (!in_array($skill_level_lower, $allowedLevels)) $skill_level_lower = 'beginner';

    $stmt = $conn->prepare(
        "INSERT INTO skills (
            user_id,
            title,
            description,
            type,
            category,
            level
        )
        VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        'isssss',
        $userId,
        $title,
        $description,
        $type,
        $category,
        $skill_level_lower
    );
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => true, 'message' => 'Skill added!', 'skill_id' => $id]);
    } else {
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Failed to add skill.']);
    }
    exit;
}

// ── GET ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id'])) {
        $userId = intval($_GET['user_id']);
        $stmt = $conn->prepare(
            "SELECT * FROM skills WHERE user_id = ? ORDER BY created_at DESC"
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $skills = [];
        while ($row = $result->fetch_assoc()) $skills[] = $row;
        $stmt->close();
    } else {
        // Explore page: show public swap requests
        $skills = [];
        
        $stmt = $conn->prepare(
            "SELECT 
                sr.id,
                sr.sender_id AS user_id,
                sr.skill_offered,
                sr.skill_wanted,
                sr.category,
                sr.skill_level,
                sr.message AS description,
                sr.status,
                sr.created_at,
                u.name AS owner_name,
                u.email AS owner_email
             FROM swap_requests sr
             JOIN users u ON sr.sender_id = u.id
             WHERE sr.receiver_id IS NULL AND sr.status = 'pending'
             ORDER BY sr.created_at DESC
             LIMIT 50"
        );
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $skills[] = $row;
        $stmt->close();
    }
    
    $conn->close();
    echo json_encode(['success' => true, 'data' => $skills]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
