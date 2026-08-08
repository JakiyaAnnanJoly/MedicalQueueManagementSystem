<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';


require_login_json();


$config = require __DIR__ . '/../config.php';
$timezoneName = (string) ($config['app_timezone'] ?? 'Asia/Dhaka');
try {
    $appTz = new DateTimeZone($timezoneName);
} catch (Throwable $e) {
    $appTz = new DateTimeZone('UTC');
}


$date = $_GET['date'] ?? '';
$location = $_GET['location'] ?? 'Main Branch';
$doctor = trim((string) ($_GET['doctor'] ?? ''));


if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid date']);
    exit;
}


$today = (new DateTimeImmutable('now', $appTz))->format('Y-m-d');
if ($date < $today) {
    http_response_code(422);
    echo json_encode(['error' => 'Past dates are not allowed']);
    exit;
}


$now = new DateTimeImmutable('now', $appTz);


$defaultSlots = [
    '07:00:00', '07:15:00', '07:30:00', '07:45:00',
    '08:30:00', '08:45:00', '09:15:00', '09:45:00',
    '10:00:00', '10:30:00', '11:00:00', '11:15:00',
    '11:30:00', '11:45:00', '14:00:00', '14:30:00',
    '15:00:00', '15:30:00',
];


$pdo = db();
$locationCheck = $pdo->prepare('SELECT id FROM locations WHERE name = :l AND is_active = 1 LIMIT 1');
$locationCheck->execute([':l' => $location]);
$locationRow = $locationCheck->fetch();
if (!$locationRow) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid location']);
    exit;
}


$locationId = (int) ($locationRow['id'] ?? 0);
$user = current_user();
if (($user['role'] ?? '') === 'patient') {
    $scopeCheck = $pdo->prepare(
        'SELECT 1
         FROM patient_locations
         WHERE patient_user_id = :uid AND location_id = :location_id
         LIMIT 1'
    );
    $scopeCheck->execute([
        ':uid' => (int) ($user['id'] ?? 0),
        ':location_id' => $locationId,
    ]);
    if (!$scopeCheck->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden location for patient']);
        exit;
    }
}


$stmt = $pdo->prepare('SELECT appointment_time FROM appointments WHERE appointment_date = :d AND location_name = :l AND status <> "cancelled"');
$stmt->execute([':d' => $date, ':l' => $location]);
$takenByLocation = array_map(static fn($row) => $row['appointment_time'], $stmt->fetchAll());


$takenByDoctor = [];
if ($doctor !== '' && $doctor !== 'Dr. On Duty') {
    $weekday = (int) (new DateTimeImmutable($date))->format('w');
    $availabilityStmt = $pdo->prepare(
        'SELECT a.start_time, a.end_time
         FROM doctor_availability a
         INNER JOIN users u ON u.id = a.doctor_user_id
         WHERE u.role = "doctor" AND u.full_name = :doctor AND a.weekday = :weekday
         ORDER BY a.start_time ASC'
    );
    $availabilityStmt->execute([
        ':doctor' => $doctor,
        ':weekday' => $weekday,
    ]);
    $availabilityRows = $availabilityStmt->fetchAll();


    $allSlots = [];
    foreach ($availabilityRows as $row) {
        $startTs = strtotime((string) $row['start_time']);
        $endTs = strtotime((string) $row['end_time']);
        if ($startTs === false || $endTs === false || $startTs >= $endTs) {
            continue;
        }
        for ($cursor = $startTs; $cursor < $endTs; $cursor += 15 * 60) {
            $allSlots[] = date('H:i:s', $cursor);
        }
    }
    $allSlots = array_values(array_unique($allSlots));


    $doctorStmt = $pdo->prepare('SELECT appointment_time FROM appointments WHERE appointment_date = :d AND doctor_name = :doctor AND status <> "cancelled"');
    $doctorStmt->execute([
        ':d' => $date,
        ':doctor' => $doctor,
    ]);
    $takenByDoctor = array_map(static fn($row) => $row['appointment_time'], $doctorStmt->fetchAll());
} else {
    $allSlots = $defaultSlots;
}


$slotStatuses = [];
foreach ($allSlots as $slot) {
    $slotDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date . ' ' . (string) $slot, $appTz);
    if (!$slotDateTime) {
        continue;
    }
    if ($slotDateTime <= $now) {
        continue;
    }


    $isTakenAtLocation = in_array($slot, $takenByLocation, true);
    $isTakenForDoctor = in_array($slot, $takenByDoctor, true);
    $slotStatuses[] = [
        'time' => $slot,
        'is_available' => !$isTakenAtLocation && !$isTakenForDoctor,
        'is_taken_location' => $isTakenAtLocation,
        'is_taken_doctor' => $isTakenForDoctor,
    ];
}


$available = array_values(array_filter($slotStatuses, static fn($slot) => (bool) $slot['is_available']));


echo json_encode([
    'date' => $date,
    'location' => $location,
    'doctor' => $doctor,
    'slots' => array_map(static fn($slot) => $slot['time'], $available),
    'slot_statuses' => $slotStatuses,
]);



