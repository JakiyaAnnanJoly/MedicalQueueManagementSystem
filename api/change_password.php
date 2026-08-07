<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';

require_login_json();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$currentPassword = (string) ($payload['current_password'] ?? '');
$newPassword = (string) ($payload['new_password'] ?? '');

if ($currentPassword === '' || strlen($newPassword) < 8) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid passwords']);
    exit;
}

$user = current_user();
$userId = (int) ($user['id'] ?? 0);

$stmt = db()->prepare('SELECT id, password_hash FROM users WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $userId]);
$row = $stmt->fetch();

if (!$row || !password_verify($currentPassword, (string) $row['password_hash'])) {
    http_response_code(422);
    echo json_encode(['error' => 'Current password is incorrect']);
    exit;
}

$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$update = db()->prepare('UPDATE users SET password_hash = :p WHERE id = :id');
$update->execute([':p' => $hash, ':id' => $userId]);

write_audit_log('password_changed', 'user', $userId, ['self_service' => true]);

echo json_encode(['success' => true]);
