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
        'SELECT u.id, u.full_name, u.username, u.profile_image_path,
                d.id AS department_id,
                d.name AS department_name
         FROM users u
         LEFT JOIN doctor_departments dd ON dd.doctor_user_id = u.id
         LEFT JOIN departments d ON d.id = dd.department_id
         WHERE u.role = "doctor"
         ORDER BY u.full_name ASC'
    )->fetchAll();


    $departments = $pdo->query('SELECT id, name, is_active FROM departments ORDER BY name ASC')->fetchAll();


    echo json_encode([
        'doctors' => $doctors,
        'departments' => $departments,
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
$departmentId = (int) ($payload['department_id'] ?? 0);


if ($doctorId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Doctor is required']);
    exit;
}


if ($departmentId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Select one department']);
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


$depStmt = $pdo->prepare('SELECT id FROM departments WHERE id = :id AND is_active = 1 LIMIT 1');
$depStmt->execute([':id' => $departmentId]);
if (!$depStmt->fetch()) {
    http_response_code(422);
    echo json_encode(['error' => 'Department is invalid']);
    exit;
}


try {
    $pdo->beginTransaction();


    $deleteStmt = $pdo->prepare('DELETE FROM doctor_departments WHERE doctor_user_id = :doctor_id');
    $deleteStmt->execute([':doctor_id' => $doctorId]);


    $insertStmt = $pdo->prepare('INSERT INTO doctor_departments (doctor_user_id, department_id) VALUES (:doctor_id, :department_id)');
    $insertStmt->execute([
        ':doctor_id' => $doctorId,
        ':department_id' => $departmentId,
    ]);


    $pdo->commit();


    write_audit_log('doctor_departments_updated', 'user', $doctorId, [
        'doctor_name' => $doctor['full_name'],
        'department_id' => $departmentId,
    ]);


    echo json_encode([
        'success' => true,
        'doctor_user_id' => $doctorId,
        'department_id' => $departmentId,
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    http_response_code(500);
    echo json_encode(['error' => 'Failed to update doctor departments']);
}



