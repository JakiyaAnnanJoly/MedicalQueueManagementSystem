<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';


require_roles_json(['admin']);


$pdo = db();


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $doctors = $pdo->query(
        'SELECT
            u.id,
            u.full_name,
            u.username,
            u.profile_image_path,
            GROUP_CONCAT(DISTINCT l.id ORDER BY l.name SEPARATOR ",") AS location_ids,
            GROUP_CONCAT(DISTINCT l.name ORDER BY l.name SEPARATOR ", ") AS locations
         FROM users u
         LEFT JOIN doctor_locations dl ON dl.doctor_user_id = u.id
         LEFT JOIN locations l ON l.id = dl.location_id
         WHERE u.role = "doctor"
         GROUP BY u.id, u.full_name, u.username, u.profile_image_path
         ORDER BY u.full_name ASC'
    )->fetchAll();


    foreach ($doctors as &$doctor) {
        $doctor['location_ids'] = $doctor['location_ids'] === null || $doctor['location_ids'] === ''
            ? []
            : array_map('intval', explode(',', (string) $doctor['location_ids']));
    }
    unset($doctor);


    $locations = $pdo->query('SELECT id, name, is_active FROM locations ORDER BY name ASC')->fetchAll();


    echo json_encode([
        'doctors' => $doctors,
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


$doctorId = (int) ($payload['doctor_user_id'] ?? 0);
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


if ($doctorId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Doctor is required']);
    exit;
}


if (!$locationIds) {
    http_response_code(422);
    echo json_encode(['error' => 'Select at least one location']);
    exit;
}


if (count($locationIds) !== 1) {
    http_response_code(422);
    echo json_encode(['error' => 'Doctor must be assigned to exactly one location']);
    exit;
}


$doctorStmt = $pdo->prepare('SELECT id, full_name FROM users WHERE id = :id AND role = "doctor" LIMIT 1');
$doctorStmt->execute([':id' => $doctorId]);
$doctor = $doctorStmt->fetch();
if (!$doctor) {
    http_response_code(404);
    echo json_encode(['error' => 'Doctor not found']);
    exit;
}


$in = implode(',', array_fill(0, count($locationIds), '?'));
$locStmt = $pdo->prepare("SELECT id, name FROM locations WHERE id IN ($in) AND is_active = 1");
$locStmt->execute($locationIds);
$validLocations = $locStmt->fetchAll();
if (count($validLocations) !== count($locationIds)) {
    http_response_code(422);
    echo json_encode(['error' => 'One or more locations are invalid']);
    exit;
}


try {
    $pdo->beginTransaction();


    $deleteStmt = $pdo->prepare('DELETE FROM doctor_locations WHERE doctor_user_id = :doctor_id');
    $deleteStmt->execute([':doctor_id' => $doctorId]);


    $insertStmt = $pdo->prepare('INSERT INTO doctor_locations (doctor_user_id, location_id) VALUES (:doctor_id, :location_id)');
    foreach ($locationIds as $locationId) {
        $insertStmt->execute([
            ':doctor_id' => $doctorId,
            ':location_id' => $locationId,
        ]);
    }


    $pdo->commit();


    write_audit_log('doctor_locations_updated', 'user', $doctorId, [
        'doctor_name' => $doctor['full_name'],
        'location_ids' => $locationIds,
    ]);


    echo json_encode([
        'success' => true,
        'doctor_user_id' => $doctorId,
        'location_ids' => $locationIds,
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    http_response_code(500);
    echo json_encode(['error' => 'Failed to update doctor locations']);
}



