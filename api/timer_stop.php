<?php
require_once 'db.php';
header('Content-Type: application/json');
$sessionId = (int)($_POST['session_id'] ?? 0);
$duration  = (int)($_POST['duration_seconds'] ?? 0);
if (!$sessionId) { echo json_encode(['error' => 'No session.']); exit; }

// Get session + billing rate
$stmt = $pdo->prepare("SELECT ps.*, ps.is_double_billing FROM parking_sessions ps WHERE ps.session_id = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$session) { echo json_encode(['error' => 'Session not found.']); exit; }

$rate = $pdo->query("SELECT rate_per_min, violation_multiplier FROM billing_rates LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$ratePerMin = (float)($rate['rate_per_min'] ?? 0.05);
$violMult   = (float)($rate['violation_multiplier'] ?? 2.00);

$minutes   = $duration / 60;
$base      = round($minutes * $ratePerMin * ($session['is_double_billing'] ? 2 : 1), 2);

$total = $base ;

$pdo->prepare("
    UPDATE parking_sessions
    SET duration_seconds=?, time_end=NOW(), base_cost=?, violation_charge=0, total_cost=?, status='awaiting_payment'
    WHERE session_id=?
")->execute([$duration, $base, $total, $sessionId]);

echo json_encode(['success' => true, 'total_cost' => $total]);