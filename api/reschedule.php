<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';


require_roles_json(['admin']);


$config = require __DIR__ . '/../config.php';
$timezoneName = (string) ($config['app_timezone'] ?? 'Asia/Dhaka');
try {
    $appTz = new DateTimeZone($timezoneName);
} catch (Throwable $e) {
    $appTz = new DateTimeZone('UTC');
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}


$payload = json_decode((string) file_get_contents('php://input'), true);
$id = (int) ($payload['appointment_id'] ?? 0);
$date = trim((string) ($payload['date'] ?? ''));
$time = trim((string) ($payload['time'] ?? ''));
$location = trim((string) ($payload['location'] ?? ''));
$doctor = trim((string) ($payload['doctor'] ?? ''));


if ($id <= 0 || $date === '' || $time === '' || $location === '' || $doctor === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}


if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid date/time']);
    exit;
}


$today = (new DateTimeImmutable('now', $appTz))->format('Y-m-d');
if ($date < $today) {
    http_response_code(422);
    echo json_encode(['error' => 'Appointment date cannot be in the past']);
    exit;
}


if (strlen($time) === 5) {
    $time .= ':00';
}


$appointmentDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date . ' ' . $time, $appTz);
if (!$appointmentDateTime) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid date/time']);
    exit;
}
if ($appointmentDateTime <= new DateTimeImmutable('now', $appTz)) {
    http_response_code(422);
    echo json_encode(['error' => 'Selected appointment time has already passed']);
    exit;
}


$pdo = db();


$locationCheck = $pdo->prepare('SELECT id FROM locations WHERE name = :name AND is_active = 1 LIMIT 1');
$locationCheck->execute([':name' => $location]);
$locationRow = $locationCheck->fetch();
if (!$locationRow) {
    http_response_code(422);
    echo json_encode(['error' => 'Selected location is invalid']);
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
        echo json_encode(['error' => 'Patient can only reschedule within assigned branches']);
        exit;
    }
}


$current = $pdo->prepare('SELECT service_name, status FROM appointments WHERE id = :id LIMIT 1');
$current->execute([':id' => $id]);
$currentRow = $current->fetch();
if (!$currentRow) {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found']);
    exit;
}


$serviceName = (string) ($currentRow['service_name'] ?? '');
$currentStatus = (string) ($currentRow['status'] ?? '');
if ($currentStatus === 'completed') {
    http_response_code(409);
    echo json_encode(['error' => 'Completed appointment cannot be rescheduled']);
    exit;
}


$doctorCheck = $pdo->prepare(
    'SELECT u.id
     FROM users u
     INNER JOIN doctor_locations dl ON dl.doctor_user_id = u.id
     INNER JOIN locations l ON l.id = dl.location_id
     INNER JOIN doctor_departments dd ON dd.doctor_user_id = u.id
     INNER JOIN departments d ON d.id = dd.department_id
     WHERE u.role = "doctor" AND u.is_active = 1 AND u.full_name = :doctor AND l.name = :location AND d.name = :service
     LIMIT 1'
);
$doctorCheck->execute([
    ':doctor' => $doctor,
    ':location' => $location,
    ':service' => $serviceName,
]);
if (!$doctorCheck->fetch()) {
    http_response_code(422);
    echo json_encode(['error' => 'Selected doctor is not available for this appointment department at selected location']);
    exit;
}


$weekday = (int) (new DateTimeImmutable($date))->format('w');
$availabilityCheck = $pdo->prepare(
    'SELECT 1
     FROM doctor_availability a
     INNER JOIN users u ON u.id = a.doctor_user_id
     WHERE u.full_name = :doctor
       AND a.weekday = :weekday
       AND :time >= a.start_time
       AND :time < a.end_time
     LIMIT 1'
);
$availabilityCheck->execute([
    ':doctor' => $doctor,
    ':weekday' => $weekday,
    ':time' => $time,
]);
if (!$availabilityCheck->fetch()) {
    http_response_code(422);
    echo json_encode(['error' => 'Selected time is outside doctor availability']);
    exit;
}


$conflict = $pdo->prepare('SELECT id FROM appointments WHERE appointment_date = :d AND appointment_time = :t AND location_name = :l AND id <> :id AND status <> "cancelled" LIMIT 1');
$conflict->execute([
    ':d' => $date,
    ':t' => $time,
    ':l' => $location,
    ':id' => $id,
]);
if ($conflict->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Target slot is already booked']);
    exit;
}


$doctorBusy = $pdo->prepare('SELECT id FROM appointments WHERE appointment_date = :d AND appointment_time = :t AND doctor_name = :doctor AND id <> :id AND status <> "cancelled" LIMIT 1');
$doctorBusy->execute([
    ':d' => $date,
    ':t' => $time,
    ':doctor' => $doctor,
    ':id' => $id,
]);
if ($doctorBusy->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Selected doctor already has an appointment at this time']);
    exit;
}


$update = $pdo->prepare('UPDATE appointments SET appointment_date = :d, appointment_time = :t, location_name = :l, doctor_name = :doc WHERE id = :id');
$update->execute([
    ':d' => $date,
    ':t' => $time,
    ':l' => $location,
    ':doc' => $doctor,
    ':id' => $id,
]);


write_audit_log('appointment_rescheduled', 'appointment', $id, [
    'date' => $date,
    'time' => $time,
    'location' => $location,
    'doctor' => $doctor,
]);


echo json_encode(['success' => true]);



