<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';


require_roles_json(['admin']);


$pdo = db();


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $staffRows = $pdo->query(
        'SELECT id, full_name, username, role, profile_image_path
         FROM users
         WHERE role IN ("doctor", "patient")
         ORDER BY role ASC, full_name ASC'
    )->fetchAll();


    $doctorMap = $pdo->query(
        'SELECT dl.doctor_user_id AS user_id,
                GROUP_CONCAT(DISTINCT l.id ORDER BY l.name SEPARATOR ",") AS location_ids,
                GROUP_CONCAT(DISTINCT l.name ORDER BY l.name SEPARATOR ", ") AS locations
         FROM doctor_locations dl
         INNER JOIN locations l ON l.id = dl.location_id
         GROUP BY dl.doctor_user_id'
    )->fetchAll();


    $patientMap = $pdo->query(
        'SELECT pl.patient_user_id AS user_id,
                GROUP_CONCAT(DISTINCT l.id ORDER BY l.name SEPARATOR ",") AS location_ids,
                GROUP_CONCAT(DISTINCT l.name ORDER BY l.name SEPARATOR ", ") AS locations
         FROM patient_locations pl
         INNER JOIN locations l ON l.id = pl.location_id
         GROUP BY pl.patient_user_id'
    )->fetchAll();


    $maps = [];
    foreach (array_merge($doctorMap, $patientMap) as $mapRow) {
        $maps[(int) $mapRow['user_id']] = [
            'location_ids' => ($mapRow['location_ids'] ?? '') === '' ? [] : array_map('intval', explode(',', (string) $mapRow['location_ids'])),
            'locations' => (string) ($mapRow['locations'] ?? ''),
        ];
    }


    foreach ($staffRows as &$staff) {
        $uid = (int) $staff['id'];
        $staff['location_ids'] = $maps[$uid]['location_ids'] ?? [];
        $staff['locations'] = $maps[$uid]['locations'] ?? '';
    }
    unset($staff);


    $locations = $pdo->query('SELECT id, name, is_active FROM locations ORDER BY name ASC')->fetchAll();


    echo json_encode([
        'staff' => $staffRows,
        'locations' => $locations,
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


$userId = (int) ($payload['user_id'] ?? 0);
$locationIdsRaw = $payload['location_ids'] ?? [];
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


if ($userId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'User is required']);
    exit;
}


if (!$locationIds) {
    http_response_code(422);
    echo json_encode(['error' => 'Select at least one location']);
    exit;
}


$userStmt = $pdo->prepare('SELECT id, full_name, role FROM users WHERE id = :id LIMIT 1');
$userStmt->execute([':id' => $userId]);
$user = $userStmt->fetch();
if (!$user || !in_array((string) $user['role'], ['doctor', 'patient'], true)) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}


$in = implode(',', array_fill(0, count($locationIds), '?'));
$locStmt = $pdo->prepare("SELECT id FROM locations WHERE id IN ($in) AND is_active = 1");
$locStmt->execute($locationIds);
if (count($locStmt->fetchAll()) !== count($locationIds)) {
    http_response_code(422);
    echo json_encode(['error' => 'One or more locations are invalid']);
    exit;
}


$role = (string) $user['role'];


if (in_array($role, ['doctor', 'patient'], true) && count($locationIds) !== 1) {
    http_response_code(422);
    echo json_encode(['error' => ucfirst($role) . ' must have exactly one location']);
    exit;
}


$table = $role === 'doctor' ? 'doctor_locations' : 'patient_locations';
$userCol = $role === 'doctor' ? 'doctor_user_id' : 'patient_user_id';


try {
    $pdo->beginTransaction();


    $deleteStmt = $pdo->prepare("DELETE FROM $table WHERE $userCol = :user_id");
    $deleteStmt->execute([':user_id' => $userId]);


    $insertStmt = $pdo->prepare("INSERT INTO $table ($userCol, location_id) VALUES (:user_id, :location_id)");
    foreach ($locationIds as $locationId) {
        $insertStmt->execute([
            ':user_id' => $userId,
            ':location_id' => $locationId,
        ]);
    }


    $pdo->commit();


    write_audit_log('staff_locations_updated', 'user', $userId, [
        'full_name' => $user['full_name'],
        'role' => $role,
        'location_ids' => $locationIds,
    ]);


    echo json_encode([
        'success' => true,
        'user_id' => $userId,
        'role' => $role,
        'location_ids' => $locationIds,
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    http_response_code(500);
    echo json_encode(['error' => 'Failed to update user locations']);
}



