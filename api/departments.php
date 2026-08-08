<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';


require_login_json();


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $pdo = db();
    $user = current_user();
    $location = trim((string) ($_GET['location'] ?? ''));
    if (in_array(strtolower($location), ['', 'all', 'all locations', 'all branches'], true)) {
        $location = '';
    }


    $params = [];
    $where = ['d.is_active = 1'];
    $join = '';


    if ($location !== '') {
        $join = 'INNER JOIN location_departments ld ON ld.department_id = d.id
                 INNER JOIN locations l ON l.id = ld.location_id';
        $where[] = 'l.name = :location_name';
        $params[':location_name'] = $location;
    }


    if (($user['role'] ?? '') === 'patient') {
        if ($join === '') {
            $join = 'INNER JOIN location_departments ld ON ld.department_id = d.id
                     INNER JOIN patient_locations pl ON pl.location_id = ld.location_id';
        } else {
            $join .= ' INNER JOIN patient_locations pl ON pl.location_id = l.id';
        }
        $where[] = 'pl.patient_user_id = :patient_id';
        $params[':patient_id'] = (int) ($user['id'] ?? 0);
    }


    $sql = 'SELECT DISTINCT d.id, d.name, d.is_active
            FROM departments d
            ' . $join . '
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY d.name ASC';


    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();


    echo json_encode(['departments' => $rows]);
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
    echo json_encode(['error' => 'Department name is required']);
    exit;
}


try {
    $pdo = db();
    $pdo->beginTransaction();


    $stmt = $pdo->prepare('INSERT INTO departments (name, is_active) VALUES (:n, 1)');
    $stmt->execute([':n' => $name]);
    $id = (int) $pdo->lastInsertId();


    $mapStmt = $pdo->prepare(
        'INSERT IGNORE INTO location_departments (location_id, department_id)
         SELECT id, :department_id
         FROM locations
         WHERE is_active = 1'
    );
    $mapStmt->execute([':department_id' => $id]);


    $pdo->commit();


    write_audit_log('department_created', 'department', $id, ['name' => $name]);
    echo json_encode(['success' => true, 'id' => $id]);
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }


    if ((int) $e->getCode() === 23000) {
        http_response_code(409);
        echo json_encode(['error' => 'Department already exists']);
        exit;
    }
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create department']);
}



