<?php
require_once 'db.php';
session_start();
header('Content-Type: application/json');
$gateId  = (int)($_POST['gate_id'] ?? 0);
$action  = trim($_POST['action'] ?? '');
$adminId = $_SESSION['admin_id'] ?? null;
if (!$gateId || !in_array($action, ['open','close'])) { echo json_encode(['error' => 'Invalid.']); exit; }
$pdo->prepare("UPDATE gates SET status=?, last_trigger_source='admin', updated_by=? WHERE gate_id=?")
    ->execute([$action === 'open' ? 'open' : 'closed', $adminId, $gateId]);
$pdo->prepare("INSERT INTO gate_logs (gate_id, action, source, admin_id) VALUES (?,?,?,?)")
    ->execute([$gateId, $action, 'admin', $adminId]);
echo json_encode(['success' => true]);