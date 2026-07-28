<?php
require_once 'db.php';
header('Content-Type: application/json');
$sessionId = (int)($_POST['session_id'] ?? 0);
if (!$sessionId) { echo json_encode(['error' => 'No session.']); exit; }
$stmt = $pdo->prepare("UPDATE parking_sessions SET time_start = NOW() WHERE session_id = ? AND time_start IS NULL");
$stmt->execute([$sessionId]);
echo json_encode(['success' => true]);