<?php
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request.']); exit;
}

$sessionId     = (int)($_POST['session_id'] ?? 0);
$paymentMethod = trim($_POST['payment_method'] ?? '');

$allowed = ['credit_card', 'mobile_pay', 'cash'];
if (!$sessionId || !in_array($paymentMethod, $allowed)) {
    echo json_encode(['error' => 'Invalid data.']); exit;
}

// Check session exists and is awaiting_payment
$stmt = $pdo->prepare("SELECT * FROM parking_sessions WHERE session_id = ? AND status = 'awaiting_payment'");
$stmt->execute([$sessionId]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    echo json_encode(['error' => 'Session not found or not ready for payment.']); exit;
}

// Check no duplicate pending payment
$stmt = $pdo->prepare("SELECT payment_id FROM payments WHERE session_id = ? AND status = 'pending'");
$stmt->execute([$sessionId]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'message' => 'Payment already submitted.']); exit;
}

// Insert payment row
$stmt = $pdo->prepare("
    INSERT INTO payments (session_id, payment_method, amount, status)
    VALUES (?, ?, ?, 'pending')
");
$stmt->execute([$sessionId, $paymentMethod, $session['total_cost']]);

echo json_encode(['success' => true]);