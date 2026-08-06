<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';

require_roles_json(['admin', 'doctor']);

$date = trim((string) ($_GET['date'] ?? ''));
$month = trim((string) ($_GET['month'] ?? ''));
$location = trim((string) ($_GET['location'] ?? ''));
$status = $_GET['status'] ?? '';

if (in_array(strtolower($location), ['', 'all', 'all locations', 'all branches'], true)) {
    $location = '';
}

if ($month !== '') {
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid month']);
        exit;
    }
    $monthStart = $month . '-01';
    $monthEnd = (new DateTimeImmutable($monthStart))->modify('last day of this month')->format('Y-m-d');
    $where = ['a.appointment_date >= :date_from', 'a.appointment_date <= :date_to'];
    $params = [':date_from' => $monthStart, ':date_to' => $monthEnd];
} else {
    if ($date === '') {
        $date = (new DateTimeImmutable('today'))->format('Y-m-d');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid date']);
        exit;
    }
    $where = ['a.appointment_date = :date'];
    $params = [':date' => $date];
}

if ($location !== '') {
    $where[] = 'a.location_name = :location';
    $params[':location'] = $location;
}

if ($status !== '') {
    $where[] = 'a.status = :status';
    $params[':status'] = $status;
}

$user = current_user();
if (($user['role'] ?? '') === 'doctor') {
    $where[] = 'a.doctor_name = :doctor_name';
    $params[':doctor_name'] = (string) ($user['full_name'] ?? '');
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

$sql = 'SELECT a.id, a.service_name, a.location_name, a.doctor_name, a.appointment_date, a.appointment_time, a.ai_priority_score,
               a.symptoms, a.notes, a.status, p.full_name, p.email, p.phone
        FROM appointments a
        INNER JOIN patients p ON p.id = a.patient_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY a.appointment_time ASC, a.ai_priority_score DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

echo json_encode(['appointments' => $rows]);
