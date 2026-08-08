<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';

require_login_json();

$roleFilter = trim((string) ($_GET['role'] ?? ''));
$allowed = ['admin', 'patient', 'doctor'];

if ($roleFilter !== '' && !in_array($roleFilter, $allowed, true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid role filter']);
    exit;
}

$user = current_user();
$params = [];
$sql = 'SELECT id, username, full_name, role, profile_image_path, is_active, created_at FROM users';
$where = [];

if ($roleFilter !== '') {
    $where[] = 'role = :r';
    $params[':r'] = $roleFilter;
}

if (($user['role'] ?? '') !== 'admin') {
    $where[] = 'is_active = 1';
}

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY role ASC, full_name ASC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

echo json_encode(['users' => $rows]);
