<?php
require_once 'db.php';
header('Content-Type: application/json');
$slotId    = (int)($_POST['slot_id'] ?? 0);
$sessionId = (int)($_POST['session_id'] ?? 0);
$enabled   = (int)($_POST['enabled'] ?? 0);
if ($slotId) {
    $pdo->prepare("UPDATE parking_slots SET is_double_billing=? WHERE slot_id=?")->execute([$enabled, $slotId]);
}
if ($sessionId) {
    $pdo->prepare("UPDATE parking_sessions SET is_double_billing=? WHERE session_id=?")->execute([$enabled, $sessionId]);
}
echo json_encode(['success' => true]);