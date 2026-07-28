<?php
require_once 'db.php';
header('Content-Type: application/json');
$stmt = $pdo->query("SELECT * FROM gates ORDER BY gate_id");
echo json_encode(['gates' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);