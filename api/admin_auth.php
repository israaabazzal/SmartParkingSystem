<?php
require_once 'db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    echo json_encode(['error' => 'Username and password are required.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$username]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// NOTE: plan uses plain-text for now; swap to password_verify() in production
if ($row && $row['password_hash'] === $password) {
    $_SESSION['admin_id']   = $row['admin_id'];
    $_SESSION['admin_user'] = $row['username'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Invalid credentials. Please try again.']);
}
