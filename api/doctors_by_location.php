<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';


require_login_json();


$locationId = (int) ($_GET['location_id'] ?? 0);
$locationName = trim((string) ($_GET['location'] ?? ''));
$departmentName = trim((string) ($_GET['department'] ?? ''));


$where = [
    'u.role = "doctor"',
    'u.is_active = 1',
    'l.is_active = 1',
];
$params = [];


$user = current_user();
if (($user['role'] ?? '') === 'patient') {
    $where[] = 'EXISTS (
        SELECT 1
        FROM patient_locations pl
        WHERE pl.patient_user_id = :patient_id
          AND pl.location_id = l.id
    )';
    $params[':patient_id'] = (int) ($user['id'] ?? 0);
}


if ($locationId > 0) {
    $where[] = 'l.id = :location_id';
    $params[':location_id'] = $locationId;
} elseif ($locationName !== '') {
    $where[] = 'l.name = :location_name';
    $params[':location_name'] = $locationName;
}


if ($departmentName !== '') {
    $where[] = 'd.name = :department_name';
    $params[':department_name'] = $departmentName;
}


$sql = 'SELECT
            u.id,
            u.full_name,
            u.profile_image_path,
            GROUP_CONCAT(DISTINCT l.name ORDER BY l.name SEPARATOR ", ") AS locations,
            GROUP_CONCAT(DISTINCT l.id ORDER BY l.name SEPARATOR ",") AS location_ids,
            MIN(d.id) AS department_id,
            MIN(d.name) AS department_name
        FROM users u
        INNER JOIN doctor_locations dl ON dl.doctor_user_id = u.id
        INNER JOIN locations l ON l.id = dl.location_id
        INNER JOIN doctor_departments dd ON dd.doctor_user_id = u.id
        INNER JOIN departments d ON d.id = dd.department_id
        WHERE ' . implode(' AND ', $where) . '
        GROUP BY u.id, u.full_name, u.profile_image_path
        ORDER BY u.full_name ASC';


$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();


foreach ($rows as &$row) {
    $row['location_ids'] = $row['location_ids'] === null || $row['location_ids'] === ''
        ? []
        : array_map('intval', explode(',', (string) $row['location_ids']));
    $row['department_ids'] = isset($row['department_id']) && (int) $row['department_id'] > 0
        ? [(int) $row['department_id']]
        : [];
}
unset($row);


echo json_encode(['doctors' => $rows]);



