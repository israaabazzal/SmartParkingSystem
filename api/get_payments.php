<?php
require_once 'db.php';
header('Content-Type: application/json');
$stmt = $pdo->query("
    SELECT p.payment_id, p.session_id, p.payment_method, p.amount,
           v.plate_number, sl.slot_code, pl.level_name
    FROM payments p
    JOIN parking_sessions ps ON ps.session_id = p.session_id
    JOIN vehicles v ON v.vehicle_id = ps.vehicle_id
    JOIN parking_slots sl ON sl.slot_id = ps.slot_id
    JOIN parking_levels pl ON pl.level_id = sl.level_id
    WHERE p.status = 'pending'
    ORDER BY p.payment_id DESC
");
echo json_encode(['payments' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);