<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';

require_roles_json(['admin', 'patient']);

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
if (!is_array($payload)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid JSON payload']);
    exit;
}

$service = trim((string) ($payload['service'] ?? ''));
$fullName = trim((string) ($payload['full_name'] ?? ''));
$email = trim((string) ($payload['email'] ?? ''));
$phone = trim((string) ($payload['phone'] ?? ''));
$location = trim((string) ($payload['location'] ?? 'Main Branch'));
$doctor = trim((string) ($payload['doctor'] ?? 'Dr. On Duty'));
$date = trim((string) ($payload['date'] ?? ''));
$time = trim((string) ($payload['time'] ?? ''));
$symptoms = trim((string) ($payload['symptoms'] ?? ''));
$notes = trim((string) ($payload['notes'] ?? ''));
$aiPriority = (int) ($payload['ai_priority_score'] ?? 1);

if (
    $service === '' || $fullName === '' || $email === '' || $phone === '' ||
    $location === '' || $date === '' || $time === ''
) {
    http_response_code(422);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid email']);
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

$aiPriority = max(1, min(5, $aiPriority));

$pdo = db();
$user = current_user();

$locationCheck = $pdo->prepare('SELECT id FROM locations WHERE name = :name AND is_active = 1 LIMIT 1');
$locationCheck->execute([':name' => $location]);
$locationRow = $locationCheck->fetch();
if (!$locationRow) {
    http_response_code(422);
    echo json_encode(['error' => 'Selected location is invalid']);
    exit;
}

$locationId = (int) ($locationRow['id'] ?? 0);

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
        echo json_encode(['error' => 'Patient can only book appointments in assigned branches']);
        exit;
    }
}

$serviceCheck = $pdo->prepare(
    'SELECT d.id
     FROM departments d
     INNER JOIN location_departments ld ON ld.department_id = d.id
     WHERE d.name = :name AND d.is_active = 1 AND ld.location_id = :location_id
     LIMIT 1'
);
$serviceCheck->execute([
    ':name' => $service,
    ':location_id' => $locationId,
]);
if (!$serviceCheck->fetch()) {
    http_response_code(422);
    echo json_encode(['error' => 'Selected department is not available for this branch']);
    exit;
}

if ($doctor !== 'Dr. On Duty') {
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
        ':service' => $service,
    ]);

    if (!$doctorCheck->fetch()) {
        http_response_code(422);
        echo json_encode(['error' => 'Selected doctor is not available for selected branch and department']);
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
}

if ($doctor !== 'Dr. On Duty') {
    $doctorBusy = $pdo->prepare(
        'SELECT id
         FROM appointments
         WHERE appointment_date = :date
           AND appointment_time = :time
           AND doctor_name = :doctor
           AND status <> "cancelled"
         LIMIT 1'
    );
    $doctorBusy->execute([
        ':date' => $date,
        ':time' => $time,
        ':doctor' => $doctor,
    ]);
    if ($doctorBusy->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Selected doctor already has an appointment in that time slot']);
        exit;
    }
}

try {
    $pdo->beginTransaction();

    $patientStmt = $pdo->prepare('SELECT id FROM patients WHERE email = :e AND phone = :p ORDER BY id DESC LIMIT 1');
    $patientStmt->execute([':e' => $email, ':p' => $phone]);
    $existing = $patientStmt->fetch();

    if ($existing) {
        $patientId = (int) $existing['id'];
        $updatePatient = $pdo->prepare('UPDATE patients SET full_name = :n WHERE id = :id');
        $updatePatient->execute([':n' => $fullName, ':id' => $patientId]);
    } else {
        $insertPatient = $pdo->prepare('INSERT INTO patients (full_name, email, phone) VALUES (:n, :e, :p)');
        $insertPatient->execute([
            ':n' => $fullName,
            ':e' => $email,
            ':p' => $phone,
        ]);
        $patientId = (int) $pdo->lastInsertId();
    }

    $insertAppointment = $pdo->prepare(
        'INSERT INTO appointments (patient_id, service_name, location_name, doctor_name, appointment_date, appointment_time, ai_priority_score, symptoms, notes)
         VALUES (:pid, :service, :location, :doctor, :adate, :atime, :priority, :symptoms, :notes)'
    );

    $insertAppointment->execute([
        ':pid' => $patientId,
        ':service' => $service,
        ':location' => $location,
        ':doctor' => $doctor,
        ':adate' => $date,
        ':atime' => $time,
        ':priority' => $aiPriority,
        ':symptoms' => $symptoms,
        ':notes' => $notes,
    ]);

    $appointmentId = (int) $pdo->lastInsertId();
    if (($user['role'] ?? '') === 'patient') {
        $_SESSION['patient_receipt_ids'] = $_SESSION['patient_receipt_ids'] ?? [];
        $_SESSION['patient_receipt_ids'][$appointmentId] = time();
    }
    $pdo->commit();

    write_audit_log('appointment_created', 'appointment', $appointmentId, [
        'service' => $service,
        'location' => $location,
        'doctor' => $doctor,
        'date' => $date,
        'time' => $time,
        'patient_email' => $email,
    ]);

    echo json_encode([
        'success' => true,
        'appointment_id' => $appointmentId,
        'receipt_url' => 'receipt.php?appointment_id=' . $appointmentId,
        'message' => 'Appointment booked successfully',
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ((int) $e->getCode() === 23000) {
        http_response_code(409);
        echo json_encode(['error' => 'Selected slot is already booked']);
        exit;
    }

    http_response_code(500);
    echo json_encode(['error' => 'Could not book appointment']);
}
