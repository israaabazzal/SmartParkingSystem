<?php
require_once 'db.php';
header('Content-Type: application/json');
$stmt = $pdo->query("
    SELECT ps.slot_id, ps.slot_code, ps.level_id, pl.level_name
    FROM parking_slots ps
    JOIN parking_levels pl ON pl.level_id = ps.level_id
    WHERE ps.status = 'available'
    ORDER BY pl.level_id, ps.slot_code
");
echo json_encode(['slots' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);