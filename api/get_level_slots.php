<?php
require_once 'db.php';
header('Content-Type: application/json');
$levels = $pdo->query("SELECT * FROM parking_levels ORDER BY level_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($levels as &$lvl) {
    $stmt = $pdo->prepare("
        SELECT ps.slot_id, ps.slot_code, ps.status, ps.is_double_billing,
               v.plate_number
        FROM parking_slots ps
        LEFT JOIN parking_sessions sess ON sess.slot_id = ps.slot_id AND sess.status IN ('active','awaiting_payment')
        LEFT JOIN vehicles v ON v.vehicle_id = sess.vehicle_id
        WHERE ps.level_id = ?
        ORDER BY ps.slot_code
    ");
    $stmt->execute([$lvl['level_id']]);
    $lvl['slots'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode(['levels' => $levels]);