<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';

require_roles_json(['admin', 'doctor']);

$date = $_GET['date'] ?? (new DateTimeImmutable('today'))->format('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid date']);
    exit;
}

$pdo = db();

$user = current_user();
$where = 'WHERE appointment_date = :date';
$params = [':date' => $date];
if (($user['role'] ?? '') === 'doctor') {
    $where .= ' AND doctor_name = :doc';
    $params[':doc'] = (string) ($user['full_name'] ?? '');
}

$countStmt = $pdo->prepare(
    'SELECT
        COUNT(*) AS total,
        SUM(status = "scheduled") AS scheduled,
        SUM(status = "checked_in") AS checked_in,
        SUM(status = "in_consultation") AS in_consultation,
        SUM(status = "completed") AS completed,
        SUM(status = "cancelled") AS cancelled,
        AVG(ai_priority_score) AS avg_priority
     FROM appointments
     ' . $where
);
$countStmt->execute($params);
$summary = $countStmt->fetch() ?: [];

$serviceStmt = $pdo->prepare(
    'SELECT service_name, COUNT(*) AS count
     FROM appointments
     ' . $where . '
     GROUP BY service_name
     ORDER BY count DESC'
);
$serviceStmt->execute($params);
$byService = $serviceStmt->fetchAll();

$locationStmt = $pdo->prepare(
    'SELECT location_name, COUNT(*) AS count
     FROM appointments
     ' . $where . '
     GROUP BY location_name
     ORDER BY count DESC'
);
$locationStmt->execute($params);
$byLocation = $locationStmt->fetchAll();

echo json_encode([
    'date' => $date,
    'summary' => $summary,
    'by_service' => $byService,
    'by_location' => $byLocation,
]);
