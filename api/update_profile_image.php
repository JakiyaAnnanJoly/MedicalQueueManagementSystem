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

if (!isset($_FILES['profile_image']) || !is_array($_FILES['profile_image'])) {
    http_response_code(422);
    echo json_encode(['error' => 'Profile image is required']);
    exit;
}

$img = $_FILES['profile_image'];
if (($img['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid profile image upload']);
    exit;
}

$tmpPath = (string) ($img['tmp_name'] ?? '');
if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
    http_response_code(422);
    echo json_encode(['error' => 'Upload validation failed']);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = (string) $finfo->file($tmpPath);
$extMap = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

if (!isset($extMap[$mime])) {
    http_response_code(422);
    echo json_encode(['error' => 'Image must be JPG, PNG, or WEBP']);
    exit;
}

$uploadsDir = realpath(__DIR__ . '/..') . '/uploads/profiles';
if ($uploadsDir === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload directory unavailable']);
    exit;
}

$filename = sprintf('%s_%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $extMap[$mime]);
$destPath = $uploadsDir . '/' . $filename;

if (!move_uploaded_file($tmpPath, $destPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save profile image']);
    exit;
}

$publicPath = 'uploads/profiles/' . $filename;
$user = current_user();
$userId = (int) ($user['id'] ?? 0);
$oldPath = trim((string) ($user['profile_image_path'] ?? ''));

$update = db()->prepare('UPDATE users SET profile_image_path = :img WHERE id = :id');
$update->execute([':img' => $publicPath, ':id' => $userId]);

set_current_profile_image($publicPath);

if ($oldPath !== '' && !str_contains($oldPath, 'default-')) {
    $oldRelative = ltrim($oldPath, '/');
    if (str_starts_with($oldRelative, 'uploads/profiles/')) {
        $oldDiskPath = realpath(__DIR__ . '/..') . '/' . $oldRelative;
    } else {
        $oldDiskPath = '';
    }
    if ($oldDiskPath && is_file($oldDiskPath)) {
        @unlink($oldDiskPath);
    }
}

write_audit_log('profile_image_updated', 'user', $userId, ['profile_image_path' => $publicPath]);

echo json_encode(['success' => true, 'profile_image_path' => $publicPath]);
