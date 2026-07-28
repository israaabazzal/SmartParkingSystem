<?php
require_once 'db.php';
session_start();
header('Content-Type: application/json');
$plate       = strtoupper(trim($_POST['plate'] ?? ''));
$vehicleType = trim($_POST['vehicle_type'] ?? 'car');
$slotId      = (int)($_POST['slot_id'] ?? 0);
if (!$plate || !$slotId) { echo json_encode(['error' => 'Missing data.']); exit; }

// Check slot available
$stmt = $pdo->prepare("SELECT slot_code FROM parking_slots WHERE slot_id = ? AND status = 'available'");
$stmt->execute([$slotId]);
$slot = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$slot) { echo json_encode(['error' => 'Slot not available.']); exit; }

// Insert or get vehicle
$stmt = $pdo->prepare("INSERT IGNORE INTO vehicles (plate_number, vehicle_type) VALUES (?,?)");
$stmt->execute([$plate, $vehicleType]);
$stmt = $pdo->prepare("SELECT vehicle_id FROM vehicles WHERE plate_number = ?");
$stmt->execute([$plate]);
$vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

// Create session
$stmt = $pdo->prepare("INSERT INTO parking_sessions (vehicle_id, slot_id, status) VALUES (?,?,'active')");
$stmt->execute([$vehicle['vehicle_id'], $slotId]);

// Mark slot occupied
$pdo->prepare("UPDATE parking_slots SET status='occupied' WHERE slot_id=?")->execute([$slotId]);

// Open entry gate
$adminId = $_SESSION['admin_id'] ?? null;
$pdo->prepare("UPDATE gates SET status='open', last_trigger_source='admin', updated_by=? WHERE gate_type='entry'")->execute([$adminId]);
$stmt = $pdo->prepare("SELECT gate_id FROM gates WHERE gate_type='entry'");
$stmt->execute();
$gate = $stmt->fetch(PDO::FETCH_ASSOC);
if ($gate) {
    $pdo->prepare("INSERT INTO gate_logs (gate_id, action, source, admin_id) VALUES (?,?,?,?)")
        ->execute([$gate['gate_id'], 'open', 'admin', $adminId]);
}

echo json_encode(['success' => true, 'slot_code' => $slot['slot_code']]);