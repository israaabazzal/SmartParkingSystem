<?php
require_once 'db.php';
header('Content-Type: application/json');

// Overall stats
$total     = $pdo->query("SELECT COUNT(*) FROM parking_slots")->fetchColumn();
$available = $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='available'")->fetchColumn();
$occupied  = $pdo->query("SELECT COUNT(*) FROM parking_slots WHERE status='occupied'")->fetchColumn();
$violations = 0;

// Per-level availability
$stmt = $pdo->query("
    SELECT
        pl.level_id,
        pl.level_name,
        pl.status,
        COUNT(ps.slot_id)                                         AS total,
        SUM(CASE WHEN ps.status = 'available' THEN 1 ELSE 0 END) AS available
    FROM parking_levels pl
    LEFT JOIN parking_slots ps ON ps.level_id = pl.level_id
    GROUP BY pl.level_id
    ORDER BY pl.level_id
");
$levels = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'total'      => (int)$total,
    'available'  => (int)$available,
    'occupied'   => (int)$occupied,
    'violations' => (int)$violations,
    'levels'     => $levels,
]);
