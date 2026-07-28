<?php
require_once 'db.php';
header('Content-Type: application/json');
$plate = strtoupper(trim($_POST['plate'] ?? ''));
if (!$plate) { echo json_encode(['error' => 'No plate provided.']); exit; }

// Check if vehicle is currently parked
$stmt = $pdo->prepare("
    SELECT ps.session_id, sl.slot_code, pl.level_name
    FROM vehicles v
    JOIN parking_sessions ps ON ps.vehicle_id = v.vehicle_id
    JOIN parking_slots sl ON sl.slot_id = ps.slot_id
    JOIN parking_levels pl ON pl.level_id = sl.level_id
    WHERE v.plate_number = ? AND ps.status IN ('active','awaiting_payment')
    LIMIT 1
");
$stmt->execute([$plate]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    echo json_encode(['status' => 'parked', 'slot_code' => $row['slot_code'], 'level_name' => $row['level_name']]);
    exit;
}
// Check if vehicle exists but not parked
$stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE plate_number = ?");
$stmt->execute([$plate]);
if ($stmt->rowCount() > 0) {
    echo json_encode(['status' => 'free']);
} else {
    echo json_encode(['status' => 'new']);
}