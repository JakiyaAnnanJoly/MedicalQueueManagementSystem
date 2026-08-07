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

$username = trim((string) ($_POST['username'] ?? ''));
$fullName = trim((string) ($_POST['full_name'] ?? ''));
$role = trim((string) ($_POST['role'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$locationIdsRaw = $_POST['location_ids'] ?? [];
$departmentIdsRaw = $_POST['department_ids'] ?? [];
$departmentIdSingle = (int) ($_POST['department_id'] ?? 0);
$workingDaysRaw = $_POST['working_days'] ?? [];
$session1Start = trim((string) ($_POST['session1_start'] ?? ''));
$session1End = trim((string) ($_POST['session1_end'] ?? ''));
$session2Start = trim((string) ($_POST['session2_start'] ?? ''));
$session2End = trim((string) ($_POST['session2_end'] ?? ''));
$locationIds = [];
if (is_array($locationIdsRaw)) {
    foreach ($locationIdsRaw as $id) {
        $intId = (int) $id;
        if ($intId > 0) {
            $locationIds[] = $intId;
        }
    }
}
$locationIds = array_values(array_unique($locationIds));
$departmentIds = [];
if (is_array($departmentIdsRaw)) {
    foreach ($departmentIdsRaw as $id) {
        $intId = (int) $id;
        if ($intId > 0) {
            $departmentIds[] = $intId;
        }
    }
}
$departmentIds = array_values(array_unique($departmentIds));
if ($departmentIdSingle > 0) {
    $departmentIds = [$departmentIdSingle];
}

$workingDays = [];
if (is_array($workingDaysRaw)) {
    foreach ($workingDaysRaw as $day) {
        $intDay = (int) $day;
        if ($intDay >= 0 && $intDay <= 6) {
            $workingDays[] = $intDay;
        }
    }
}
$workingDays = array_values(array_unique($workingDays));

if ($username === '' || $fullName === '' || $role === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9_\-.]{3,50}$/', $username)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid username format']);
    exit;
}

if (!in_array($role, ['patient', 'doctor'], true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Role must be patient or doctor']);
    exit;
}

if (strlen($password) < 8) {
    http_response_code(422);
    echo json_encode(['error' => 'Password must be at least 8 characters']);
    exit;
}

if (in_array($role, ['doctor', 'patient'], true) && !$locationIds) {
    http_response_code(422);
    echo json_encode(['error' => 'User must be assigned to at least one location']);
    exit;
}

if ($role === 'patient' && count($locationIds) !== 1) {
    http_response_code(422);
    echo json_encode(['error' => 'Patient must be assigned to exactly one location']);
    exit;
}

if ($role === 'doctor' && count($locationIds) !== 1) {
    http_response_code(422);
    echo json_encode(['error' => 'Doctor must be assigned to exactly one location']);
    exit;
}

if ($role === 'doctor' && count($departmentIds) !== 1) {
    http_response_code(422);
    echo json_encode(['error' => 'Doctor must be assigned to exactly one department']);
    exit;
}

if ($role === 'doctor') {
    if (!$workingDays) {
        http_response_code(422);
        echo json_encode(['error' => 'Doctor working days are required']);
        exit;
    }

    if (!preg_match('/^\d{2}:\d{2}$/', $session1Start) || !preg_match('/^\d{2}:\d{2}$/', $session1End)) {
        http_response_code(422);
        echo json_encode(['error' => 'Doctor session 1 start/end times are required']);
        exit;
    }

    if ($session1Start >= $session1End) {
        http_response_code(422);
        echo json_encode(['error' => 'Session 1 end time must be after start time']);
        exit;
    }

    if (($session2Start !== '' || $session2End !== '') && (!preg_match('/^\d{2}:\d{2}$/', $session2Start) || !preg_match('/^\d{2}:\d{2}$/', $session2End))) {
        http_response_code(422);
        echo json_encode(['error' => 'Session 2 times must be valid HH:MM format']);
        exit;
    }

    if (($session2Start !== '' || $session2End !== '') && $session2Start >= $session2End) {
        http_response_code(422);
        echo json_encode(['error' => 'Session 2 end time must be after start time']);
        exit;
    }

    if ($session2Start !== '' && $session2End !== '') {
        $overlapsSession1 = !($session2End <= $session1Start || $session2Start >= $session1End);
        if ($overlapsSession1) {
            http_response_code(422);
            echo json_encode(['error' => 'Session 2 overlaps with Session 1']);
            exit;
        }
    }
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
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $pdo = db();

    if (in_array($role, ['doctor', 'patient'], true)) {
        $in = implode(',', array_fill(0, count($locationIds), '?'));
        $locStmt = $pdo->prepare("SELECT id FROM locations WHERE id IN ($in) AND is_active = 1");
        $locStmt->execute($locationIds);
        if (count($locStmt->fetchAll()) !== count($locationIds)) {
            @unlink($destPath);
            http_response_code(422);
            echo json_encode(['error' => 'Invalid location selection']);
            exit;
        }
    }

    if ($role === 'doctor') {
        $depStmt = $pdo->prepare('SELECT id FROM departments WHERE id = :id AND is_active = 1 LIMIT 1');
        $depStmt->execute([':id' => $departmentIds[0]]);
        if (!$depStmt->fetch()) {
            @unlink($destPath);
            http_response_code(422);
            echo json_encode(['error' => 'Invalid department selection for doctor']);
            exit;
        }
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password_hash, full_name, role, profile_image_path, is_kiosk_account, kiosk_label, is_active)
         VALUES (:u, :p, :n, :r, :img, :is_kiosk, :kiosk_label, 1)'
    );
    $stmt->execute([
        ':u' => $username,
        ':p' => $hash,
        ':n' => $fullName,
        ':r' => $role,
        ':img' => $publicPath,
        ':is_kiosk' => $role === 'patient' ? 1 : 0,
        ':kiosk_label' => $role === 'patient' ? $fullName : null,
    ]);

    $newId = (int) $pdo->lastInsertId();

    if ($role === 'doctor') {
        $mapStmt = $pdo->prepare('INSERT INTO doctor_locations (doctor_user_id, location_id) VALUES (:doctor_id, :location_id)');
        foreach ($locationIds as $locationId) {
            $mapStmt->execute([
                ':doctor_id' => $newId,
                ':location_id' => $locationId,
            ]);
        }

        $depMapStmt = $pdo->prepare('INSERT INTO doctor_departments (doctor_user_id, department_id) VALUES (:doctor_id, :department_id)');
        $depMapStmt->execute([
            ':doctor_id' => $newId,
            ':department_id' => $departmentIds[0],
        ]);

        $availabilityRows = [];
        foreach ($workingDays as $weekday) {
            $availabilityRows[] = ['weekday' => $weekday, 'start' => $session1Start . ':00', 'end' => $session1End . ':00'];
            if ($session2Start !== '' && $session2End !== '') {
                $availabilityRows[] = ['weekday' => $weekday, 'start' => $session2Start . ':00', 'end' => $session2End . ':00'];
            }
        }

        $availabilityStmt = $pdo->prepare(
            'INSERT INTO doctor_availability (doctor_user_id, weekday, start_time, end_time)
             VALUES (:doctor_id, :weekday, :start_time, :end_time)'
        );
        foreach ($availabilityRows as $row) {
            $availabilityStmt->execute([
                ':doctor_id' => $newId,
                ':weekday' => $row['weekday'],
                ':start_time' => $row['start'],
                ':end_time' => $row['end'],
            ]);
        }
    } elseif ($role === 'patient') {
        $mapStmt = $pdo->prepare('INSERT INTO patient_locations (patient_user_id, location_id) VALUES (:patient_id, :location_id)');
        foreach ($locationIds as $locationId) {
            $mapStmt->execute([
                ':patient_id' => $newId,
                ':location_id' => $locationId,
            ]);
        }
    }

    $pdo->commit();

    write_audit_log('user_created', 'user', $newId, [
        'username' => $username,
        'role' => $role,
        'profile_image_path' => $publicPath,
        'location_ids' => $locationIds,
        'department_ids' => $departmentIds,
        'working_days' => $workingDays,
        'session1' => $session1Start !== '' && $session1End !== '' ? $session1Start . '-' . $session1End : null,
        'session2' => $session2Start !== '' && $session2End !== '' ? $session2Start . '-' . $session2End : null,
    ]);

    echo json_encode([
        'success' => true,
        'user_id' => $newId,
        'profile_image_path' => $publicPath,
    ]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) $e->getCode() === 23000) {
        @unlink($destPath);
        http_response_code(409);
        echo json_encode(['error' => 'Username already exists']);
        exit;
    }

    @unlink($destPath);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create user']);
}
