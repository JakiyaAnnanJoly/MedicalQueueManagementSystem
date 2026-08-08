<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';

require_login_json();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = current_user();
    if (($user['role'] ?? '') === 'patient') {
        $stmt = db()->prepare(
            'SELECT l.id, l.name, l.is_active
             FROM locations l
             INNER JOIN patient_locations pl ON pl.location_id = l.id
             WHERE pl.patient_user_id = :uid
             ORDER BY l.name ASC'
        );
        $stmt->execute([':uid' => (int) ($user['id'] ?? 0)]);
        $rows = $stmt->fetchAll();
    } elseif (($user['role'] ?? '') === 'doctor') {
        $stmt = db()->prepare(
            'SELECT l.id, l.name, l.is_active
             FROM locations l
             INNER JOIN doctor_locations dl ON dl.location_id = l.id
             WHERE dl.doctor_user_id = :uid
             ORDER BY l.name ASC'
        );
        $stmt->execute([':uid' => (int) ($user['id'] ?? 0)]);
        $rows = $stmt->fetchAll();
    } else {
        $rows = db()->query('SELECT id, name, is_active FROM locations ORDER BY name ASC')->fetchAll();
    }
    echo json_encode(['locations' => $rows]);
    exit;
}

require_roles_json(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$name = trim((string) ($payload['name'] ?? ''));
if ($name === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Location name is required']);
    exit;
}

try {
    $pdo = db();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO locations (name, is_active) VALUES (:n, 1)');
    $stmt->execute([':n' => $name]);
    $id = (int) $pdo->lastInsertId();

    $mapStmt = $pdo->prepare(
        'INSERT IGNORE INTO location_departments (location_id, department_id)
         SELECT :location_id, id
         FROM departments
         WHERE is_active = 1'
    );
    $mapStmt->execute([':location_id' => $id]);

    $pdo->commit();

    write_audit_log('location_created', 'location', $id, ['name' => $name]);
    echo json_encode(['success' => true, 'id' => $id]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) $e->getCode() === 23000) {
        http_response_code(409);
        echo json_encode(['error' => 'Location already exists']);
        exit;
    }
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create location']);
}
