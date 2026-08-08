<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';


require_roles_json(['admin']);


$pdo = db();


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $locations = $pdo->query('SELECT id, name, is_active FROM locations ORDER BY name ASC')->fetchAll();
    $departments = $pdo->query('SELECT id, name, is_active FROM departments ORDER BY name ASC')->fetchAll();


    $mappings = $pdo->query(
        'SELECT l.id AS location_id,
                l.name AS location_name,
                GROUP_CONCAT(d.id ORDER BY d.name SEPARATOR ",") AS department_ids,
                GROUP_CONCAT(d.name ORDER BY d.name SEPARATOR ", ") AS department_names
         FROM locations l
         LEFT JOIN location_departments ld ON ld.location_id = l.id
         LEFT JOIN departments d ON d.id = ld.department_id
         GROUP BY l.id, l.name
         ORDER BY l.name ASC'
    )->fetchAll();


    foreach ($mappings as &$mapping) {
        $mapping['department_ids'] = ($mapping['department_ids'] ?? '') === ''
            ? []
            : array_map('intval', explode(',', (string) $mapping['department_ids']));
    }
    unset($mapping);


    echo json_encode([
        'locations' => $locations,
        'departments' => $departments,
        'mappings' => $mappings,
    ]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}


$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}


$locationId = (int) ($payload['location_id'] ?? 0);
$departmentIdsRaw = $payload['department_ids'] ?? [];
$departmentIds = [];
if (is_array($departmentIdsRaw)) {
    foreach ($departmentIdsRaw as $depId) {
        $id = (int) $depId;
        if ($id > 0) {
            $departmentIds[] = $id;
        }
    }
}
$departmentIds = array_values(array_unique($departmentIds));


if ($locationId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Location is required']);
    exit;
}


if (!$departmentIds) {
    http_response_code(422);
    echo json_encode(['error' => 'Select at least one department']);
    exit;
}


$locationStmt = $pdo->prepare('SELECT id, name FROM locations WHERE id = :id LIMIT 1');
$locationStmt->execute([':id' => $locationId]);
$location = $locationStmt->fetch();
if (!$location) {
    http_response_code(404);
    echo json_encode(['error' => 'Location not found']);
    exit;
}


$in = implode(',', array_fill(0, count($departmentIds), '?'));
$depStmt = $pdo->prepare("SELECT id FROM departments WHERE id IN ($in) AND is_active = 1");
$depStmt->execute($departmentIds);
if (count($depStmt->fetchAll()) !== count($departmentIds)) {
    http_response_code(422);
    echo json_encode(['error' => 'One or more departments are invalid']);
    exit;
}


try {
    $pdo->beginTransaction();


    $deleteStmt = $pdo->prepare('DELETE FROM location_departments WHERE location_id = :location_id');
    $deleteStmt->execute([':location_id' => $locationId]);


    $insertStmt = $pdo->prepare('INSERT INTO location_departments (location_id, department_id) VALUES (:location_id, :department_id)');
    foreach ($departmentIds as $departmentId) {
        $insertStmt->execute([
            ':location_id' => $locationId,
            ':department_id' => $departmentId,
        ]);
    }


    $pdo->commit();


    write_audit_log('location_departments_updated', 'location', $locationId, [
        'location_name' => $location['name'],
        'department_ids' => $departmentIds,
    ]);


    echo json_encode([
        'success' => true,
        'location_id' => $locationId,
        'department_ids' => $departmentIds,
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    http_response_code(500);
    echo json_encode(['error' => 'Failed to update location departments']);
}



