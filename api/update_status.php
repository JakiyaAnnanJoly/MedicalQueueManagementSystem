<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';

require_roles_json(['admin', 'doctor']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
$id = (int) ($payload['appointment_id'] ?? 0);
$status = trim((string) ($payload['status'] ?? ''));
$notes = trim((string) ($payload['notes'] ?? ''));

$valid = ['scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled'];
if ($id <= 0 || !in_array($status, $valid, true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$user = current_user();
$pdo = db();

if (($user['role'] ?? '') === 'doctor') {
    if (!in_array($status, ['in_consultation', 'completed'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Doctor can only set in_consultation or completed']);
        exit;
    }
}

$appointmentStmt = $pdo->prepare('SELECT id, status, doctor_name FROM appointments WHERE id = :id LIMIT 1');
$appointmentStmt->execute([':id' => $id]);
$appointment = $appointmentStmt->fetch();
if (!$appointment) {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found']);
    exit;
}

if (($user['role'] ?? '') === 'doctor' && (string) ($appointment['doctor_name'] ?? '') !== (string) ($user['full_name'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Doctor can only update own appointments']);
    exit;
}

if ((string) ($appointment['status'] ?? '') === 'completed') {
    http_response_code(409);
    echo json_encode(['error' => 'Completed appointment cannot be updated']);
    exit;
}

$stmt = $pdo->prepare('UPDATE appointments SET status = :status, notes = CASE WHEN :notes = "" THEN notes ELSE :notes END WHERE id = :id');
$stmt->execute([
    ':status' => $status,
    ':notes' => $notes,
    ':id' => $id,
]);

write_audit_log('appointment_status_updated', 'appointment', $id, [
    'new_status' => $status,
    'notes_provided' => $notes !== '',
]);

echo json_encode(['success' => true]);
