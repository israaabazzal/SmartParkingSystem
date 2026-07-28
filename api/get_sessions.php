<?php
require_once 'db.php';
header('Content-Type: application/json');
$stmt = $pdo->query("
    SELECT ps.session_id, ps.status, ps.time_start, ps.duration_seconds,
           ps.total_cost, ps.is_double_billing,
           ps.slot_id,
           sl.slot_code, pl.level_name,
           v.plate_number
    FROM parking_sessions ps
    JOIN parking_slots sl ON sl.slot_id = ps.slot_id
    JOIN parking_levels pl ON pl.level_id = sl.level_id
    JOIN vehicles v ON v.vehicle_id = ps.vehicle_id
    WHERE ps.status IN ('active','awaiting_payment')
    ORDER BY pl.level_id, sl.slot_code
");
echo json_encode(['sessions' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);