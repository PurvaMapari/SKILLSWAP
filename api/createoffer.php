<?php

require_once 'config.php';

$conn = getConnection();

$data = json_decode(file_get_contents("php://input"), true);

$user_id = intval($data['user_id']);
$title = trim($data['title']);
$description = trim($data['description']);
$type = trim($data['type']);
$category = trim($data['category']);
$level = trim($data['level']);

if (!$user_id || !$title || !$type) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required fields"
    ]);
    exit;
}

$stmt = $conn->prepare("
INSERT INTO skills
(user_id, title, description, type, category, level)
VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isssss",
    $user_id,
    $title,
    $description,
    $type,
    $category,
    $level
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Skill added successfully"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => $stmt->error
    ]);
}
?>