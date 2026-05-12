<?php
// ============================================================
//  api/admin.php  —  Admin Panel API
//  POST: action=login   { username, password }
//  GET:  action=stats   → dashboard statistics
//  GET:  action=users   → all users
//  GET:  action=swaps   → all swap requests
//  GET:  action=skills  → all listed skills
// ============================================================
require_once 'config.php';

$conn = getConnection();

// Admin credentials (from .env or defaults)
$ADMIN_USER = getenv('ADMIN_USER') ?: 'admin';
$ADMIN_PASS = getenv('ADMIN_PASS') ?: 'skillswap2025';

// ── POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    $action = trim($data['action'] ?? '');

    // ── LOGIN ──
    if ($action === 'login') {
        $username = trim($data['username'] ?? '');
        $password = trim($data['password'] ?? '');

        if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful.',
                'token'   => base64_encode($ADMIN_USER . ':' . md5($ADMIN_PASS . date('Y-m-d')))
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        }
        $conn->close();
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    $conn->close();
    exit;
}

// ── GET ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = trim($_GET['action'] ?? '');

    // ── DASHBOARD STATS ──
    if ($action === 'stats') {
        $stats = [];

        // Total users
        $r = $conn->query("SELECT COUNT(*) AS c FROM users");
        $stats['total_users'] = $r->fetch_assoc()['c'];

        // New users (last 7 days)
        $r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['new_users_7d'] = $r->fetch_assoc()['c'];

        // Total skills listed
        $r = $conn->query("SELECT COUNT(*) AS c FROM skills");
        $stats['total_skills'] = $r->fetch_assoc()['c'];

        // Total swap requests (with receiver)
        $r = $conn->query("SELECT COUNT(*) AS c FROM swap_requests WHERE receiver_id IS NOT NULL");
        $stats['total_swaps'] = $r->fetch_assoc()['c'];

        // Public listings (no receiver)
        $r = $conn->query("SELECT COUNT(*) AS c FROM swap_requests WHERE receiver_id IS NULL AND status = 'pending'");
        $stats['public_listings'] = $r->fetch_assoc()['c'];

        // Pending swaps
        $r = $conn->query("SELECT COUNT(*) AS c FROM swap_requests WHERE status = 'pending' AND receiver_id IS NOT NULL");
        $stats['pending_swaps'] = $r->fetch_assoc()['c'];

        // Accepted swaps
        $r = $conn->query("SELECT COUNT(*) AS c FROM swap_requests WHERE status = 'accepted'");
        $stats['accepted_swaps'] = $r->fetch_assoc()['c'];

        // Declined swaps
        $r = $conn->query("SELECT COUNT(*) AS c FROM swap_requests WHERE status = 'declined'");
        $stats['declined_swaps'] = $r->fetch_assoc()['c'];

        // Top categories
        $r = $conn->query("SELECT category, COUNT(*) AS c FROM swap_requests WHERE category != '' GROUP BY category ORDER BY c DESC LIMIT 5");
        $cats = [];
        while ($row = $r->fetch_assoc()) $cats[] = $row;
        $stats['top_categories'] = $cats;

        // Recent activity (last 10 swaps)
        $r = $conn->query("
            SELECT sr.id, sr.skill_offered, sr.skill_wanted, sr.status, sr.created_at,
                   s.name AS sender_name, r.name AS receiver_name
            FROM swap_requests sr
            LEFT JOIN users s ON sr.sender_id = s.id
            LEFT JOIN users r ON sr.receiver_id = r.id
            ORDER BY sr.created_at DESC
            LIMIT 10
        ");
        $recent = [];
        while ($row = $r->fetch_assoc()) $recent[] = $row;
        $stats['recent_activity'] = $recent;

        echo json_encode(['success' => true, 'data' => $stats]);
        $conn->close();
        exit;
    }

    // ── ALL USERS ──
    if ($action === 'users') {
        $r = $conn->query("
            SELECT u.id, u.name, u.email, u.location, u.phone, u.created_at,
                   (SELECT COUNT(*) FROM skills WHERE user_id = u.id) AS skill_count,
                   (SELECT COUNT(*) FROM swap_requests WHERE sender_id = u.id OR receiver_id = u.id) AS swap_count
            FROM users u
            ORDER BY u.created_at DESC
        ");
        $users = [];
        while ($row = $r->fetch_assoc()) $users[] = $row;
        echo json_encode(['success' => true, 'data' => $users]);
        $conn->close();
        exit;
    }

    // ── ALL SWAPS ──
    if ($action === 'swaps') {
        $r = $conn->query("
            SELECT sr.id, sr.skill_offered, sr.skill_wanted, sr.category, sr.status, sr.message, sr.created_at,
                   s.name AS sender_name, r.name AS receiver_name
            FROM swap_requests sr
            LEFT JOIN users s ON sr.sender_id = s.id
            LEFT JOIN users r ON sr.receiver_id = r.id
            ORDER BY sr.created_at DESC
        ");
        $swaps = [];
        while ($row = $r->fetch_assoc()) $swaps[] = $row;
        echo json_encode(['success' => true, 'data' => $swaps]);
        $conn->close();
        exit;
    }

    // ── ALL SKILLS ──
    if ($action === 'skills') {
        $r = $conn->query("
            SELECT sk.id, sk.title, sk.type, sk.category, sk.level, sk.created_at,
                   u.name AS owner_name
            FROM skills sk
            LEFT JOIN users u ON sk.user_id = u.id
            ORDER BY sk.created_at DESC
        ");
        $skills = [];
        while ($row = $r->fetch_assoc()) $skills[] = $row;
        echo json_encode(['success' => true, 'data' => $skills]);
        $conn->close();
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
