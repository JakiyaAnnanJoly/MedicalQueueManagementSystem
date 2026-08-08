<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';

require_roles_json(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$userId = (int) ($payload['user_id'] ?? 0);
$newPassword = (string) ($payload['new_password'] ?? '');

if ($userId <= 0 || strlen($newPassword) < 8) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid user id or weak password']);
    exit;
}

$hash = password_hash($newPassword, PASSWORD_DEFAULT);
$stmt = db()->prepare('UPDATE users SET password_hash = :p WHERE id = :id');
$stmt->execute([':p' => $hash, ':id' => $userId]);

if ($stmt->rowCount() < 1) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

write_audit_log('password_reset', 'user', $userId, ['by_admin' => true]);

echo json_encode(['success' => true]);
