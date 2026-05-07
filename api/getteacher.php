<?php

require_once 'config.php';

$conn = getConnection();

$id = intval($_GET['id'] ?? 0);

$sql = "
SELECT
  name,
  avatar_url,
  average_rating,
  total_reviews,
  completed_swaps,
  availability
FROM users
WHERE id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param('i', $id);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

echo json_encode([
  'success' => true,
  'data' => $user
]);