<?php
// ============================================================
//  api/signup.php  —  Register a new user
//  METHOD: POST
//  BODY  : { name, email, password }
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed.']);
    exit;
}

// Accept JSON body OR normal form POST
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$name     = trim($data['name']     ?? '');
$email    = trim($data['email']    ?? '');
$password = trim($data['password'] ?? '');

// ── Validation ──────────────────────────────
if (!$name || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}
if (strlen($name) < 2) {
    echo json_encode(['success' => false, 'message' => 'Name must be at least 2 characters.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

$conn = getConnection();

// ── Check duplicate email ────────────────────
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Email already registered. Try logging in.']);
    exit;
}
$stmt->close();

// ── Hash password & insert ───────────────────
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $name, $email, $hash);

if (!$stmt->execute()) {
    die("SQL ERROR: " . $stmt->error);
}

$userId = $conn->insert_id;
$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'message' => 'Account created successfully! Redirecting...',
    'user'    => ['id' => $userId, 'name' => $name, 'email' => $email]
]);
