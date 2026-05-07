<?php
// Get skills for a specific user
require_once 'config.php';

$conn = getConnection();

$user_id = intval($_GET['user_id'] ?? 0);

if (!$user_id) {
    echo json_encode(["success" => false, "message" => "user_id required.", "data" => []]);
    exit;
}

$stmt = $conn->prepare("SELECT id, title, description, type, category, level as skill_level FROM skills WHERE user_id = ? AND type = 'offer' ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$skills = [];
while ($row = $result->fetch_assoc()) {
    $skills[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "data" => $skills
]);
?>