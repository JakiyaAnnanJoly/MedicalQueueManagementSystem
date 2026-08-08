<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';


require_login_json();


$date = $_GET['date'] ?? (new DateTimeImmutable('today'))->format('Y-m-d');
$location = trim((string) ($_GET['location'] ?? ''));


if (in_array(strtolower($location), ['', 'all', 'all locations', 'all branches'], true)) {
    $location = '';
}


if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid date']);
    exit;
}


$user = current_user();
$where = [
    'a.appointment_date = :date',
    'a.status IN ("scheduled", "checked_in", "in_consultation")',
];
$params = [':date' => $date];


if ($location !== '') {
    $where[] = 'a.location_name = :location';
    $params[':location'] = $location;
}


if (($user['role'] ?? '') === 'doctor') {
    $where[] = 'a.doctor_name = :doctor';
    $params[':doctor'] = (string) ($user['full_name'] ?? '');
} elseif (($user['role'] ?? '') === 'patient') {
    $where[] = 'EXISTS (
        SELECT 1
        FROM patient_locations pl
        INNER JOIN locations l ON l.id = pl.location_id
        WHERE pl.patient_user_id = :patient_id
          AND l.name = a.location_name
    )';
    $params[':patient_id'] = (int) ($user['id'] ?? 0);
}


$sql = 'SELECT a.id, p.full_name, p.phone, a.service_name, a.doctor_name, a.location_name, a.appointment_time, a.ai_priority_score, a.status
        FROM appointments a
        INNER JOIN patients p ON p.id = a.patient_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY a.ai_priority_score DESC, a.appointment_time ASC';


$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();


$token = 1;
foreach ($rows as &$row) {
    $row['token_no'] = $token++;
}
unset($row);


echo json_encode([
    'date' => $date,
    'location' => $location === '' ? 'All locations' : $location,
    'queue' => $rows,
]);



