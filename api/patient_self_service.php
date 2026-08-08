<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';


require_roles_json(['patient']);


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


$action = trim((string) ($payload['action'] ?? 'lookup'));
$phone = trim((string) ($payload['phone'] ?? ''));
$email = trim((string) ($payload['email'] ?? ''));
$appointmentId = (int) ($payload['appointment_id'] ?? 0);


if ($phone === '' && $email === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Phone or email is required']);
    exit;
}


if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid email']);
    exit;
}


$user = current_user();
$pdo = db();


$contactWhere = [];
$params = [
    ':kiosk_user_id' => (int) ($user['id'] ?? 0),
    ':today' => (new DateTimeImmutable('today'))->format('Y-m-d'),
];


if ($phone !== '') {
    $contactWhere[] = 'p.phone = :phone';
    $params[':phone'] = $phone;
}
if ($email !== '') {
    $contactWhere[] = 'p.email = :email';
    $params[':email'] = $email;
}


$baseSql = 'SELECT a.id, a.service_name, a.location_name, a.doctor_name, a.appointment_date, a.appointment_time,
                  a.ai_priority_score, a.status, p.full_name, p.email, p.phone
           FROM appointments a
           INNER JOIN patients p ON p.id = a.patient_id
           INNER JOIN locations l ON l.name = a.location_name
           INNER JOIN patient_locations pl ON pl.location_id = l.id
           WHERE pl.patient_user_id = :kiosk_user_id
             AND a.appointment_date >= :today
             AND (' . implode(' OR ', $contactWhere) . ')';


if ($action === 'lookup') {
    $stmt = $pdo->prepare($baseSql . '
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 10');
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $_SESSION['patient_receipt_ids'] = $_SESSION['patient_receipt_ids'] ?? [];
    foreach ($rows as $row) {
        $_SESSION['patient_receipt_ids'][(int) $row['id']] = time();
    }
    echo json_encode(['appointments' => $rows]);
    exit;
}


if (!in_array($action, ['check_in', 'checkout', 'cancel'], true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid self-service action']);
    exit;
}


if ($appointmentId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Appointment is required']);
    exit;
}


$params[':appointment_id'] = $appointmentId;
$stmt = $pdo->prepare($baseSql . ' AND a.id = :appointment_id LIMIT 1');
$stmt->execute($params);
$appointment = $stmt->fetch();


if (!$appointment) {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found for the provided contact details']);
    exit;
}


$currentStatus = (string) ($appointment['status'] ?? '');
$targetStatus = match ($action) {
    'check_in' => 'checked_in',
    'checkout' => 'completed',
    'cancel' => 'cancelled',
};


$allowedTransitions = [
    'check_in' => ['scheduled'],
    'checkout' => ['checked_in', 'in_consultation'],
    'cancel' => ['scheduled', 'checked_in'],
];


if (!in_array($currentStatus, $allowedTransitions[$action], true)) {
    http_response_code(409);
    echo json_encode(['error' => 'This action is not available for the current appointment status']);
    exit;
}


$update = $pdo->prepare('UPDATE appointments SET status = :status WHERE id = :id');
$update->execute([
    ':status' => $targetStatus,
    ':id' => $appointmentId,
]);


$_SESSION['patient_receipt_ids'] = $_SESSION['patient_receipt_ids'] ?? [];
$_SESSION['patient_receipt_ids'][$appointmentId] = time();


write_audit_log('patient_self_service_' . $action, 'appointment', $appointmentId, [
    'from_status' => $currentStatus,
    'to_status' => $targetStatus,
    'kiosk_user_id' => (int) ($user['id'] ?? 0),
    'location' => (string) ($appointment['location_name'] ?? ''),
]);


echo json_encode([
    'success' => true,
    'appointment_id' => $appointmentId,
    'status' => $targetStatus,
]);



