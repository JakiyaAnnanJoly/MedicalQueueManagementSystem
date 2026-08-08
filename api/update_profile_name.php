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

$user = current_user();
$role = (string) ($user['role'] ?? '');
if (!in_array($role, ['patient', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['error' => 'Only admin or patient can update this field']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$fullName = trim((string) ($payload['full_name'] ?? ''));

if ($fullName === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Full name is required']);
    exit;
}

if (mb_strlen($fullName) < 2 || mb_strlen($fullName) > 120) {
    http_response_code(422);
    echo json_encode(['error' => 'Full name must be between 2 and 120 characters']);
    exit;
}

$fullName = preg_replace('/\s+/', ' ', $fullName) ?? $fullName;
$userId = (int) ($user['id'] ?? 0);

$update = db()->prepare('UPDATE users SET full_name = :name WHERE id = :id');
$update->execute([
    ':name' => $fullName,
    ':id' => $userId,
]);

set_current_full_name($fullName);

write_audit_log('profile_name_updated', 'user', $userId, ['full_name' => $fullName]);

echo json_encode(['success' => true, 'full_name' => $fullName]);
