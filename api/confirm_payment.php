<?php
require_once 'db.php';
session_start();
header('Content-Type: application/json');
$paymentId = (int)($_POST['payment_id'] ?? 0);
$sessionId = (int)($_POST['session_id'] ?? 0);
$adminId   = $_SESSION['admin_id'] ?? null;
if (!$paymentId || !$sessionId) { echo json_encode(['error' => 'Invalid.']); exit; }

// Confirm payment
$pdo->prepare("UPDATE payments SET status='paid', paid_at=NOW() WHERE payment_id=?")->execute([$paymentId]);

// Complete session
$pdo->prepare("UPDATE parking_sessions SET status='completed' WHERE session_id=?")->execute([$sessionId]);

// Free the slot
$stmt = $pdo->prepare("SELECT slot_id FROM parking_sessions WHERE session_id=?");
$stmt->execute([$sessionId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $pdo->prepare("UPDATE parking_slots SET status='available' WHERE slot_id=?")->execute([$row['slot_id']]);
}

// Open exit gate
$pdo->prepare("UPDATE gates SET status='open', last_trigger_source='system', updated_by=? WHERE gate_type='exit'")->execute([$adminId]);
$stmt = $pdo->prepare("SELECT gate_id FROM gates WHERE gate_type='exit'");
$stmt->execute();
$gate = $stmt->fetch(PDO::FETCH_ASSOC);
if ($gate) {
    $pdo->prepare("INSERT INTO gate_logs (gate_id, action, source, admin_id) VALUES (?,?,?,?)")
        ->execute([$gate['gate_id'], 'open', 'system', $adminId]);
}
echo json_encode(['success' => true]);