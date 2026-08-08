<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';


require_roles_json(['doctor']);


$pdo = db();
$user = current_user();
$doctorId = (int) ($user['id'] ?? 0);


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare(
        'SELECT id, weekday, start_time, end_time
         FROM doctor_availability
         WHERE doctor_user_id = :doctor_id
         ORDER BY weekday ASC, start_time ASC'
    );
    $stmt->execute([':doctor_id' => $doctorId]);


    echo json_encode([
        'availability' => $stmt->fetchAll(),
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


$action = trim((string) ($payload['action'] ?? ''));


if ($action === 'add') {
    $weekday = (int) ($payload['weekday'] ?? -1);
    $start = trim((string) ($payload['start_time'] ?? ''));
    $end = trim((string) ($payload['end_time'] ?? ''));


    if ($weekday < 0 || $weekday > 6) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid weekday']);
        exit;
    }


    if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $start) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $end)) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid time format']);
        exit;
    }


    if (strlen($start) === 5) {
        $start .= ':00';
    }
    if (strlen($end) === 5) {
        $end .= ':00';
    }


    if ($start >= $end) {
        http_response_code(422);
        echo json_encode(['error' => 'End time must be after start time']);
        exit;
    }


    $overlapStmt = $pdo->prepare(
        'SELECT id
         FROM doctor_availability
         WHERE doctor_user_id = :doctor_id
           AND weekday = :weekday
           AND NOT (end_time <= :start_time OR start_time >= :end_time)
         LIMIT 1'
    );
    $overlapStmt->execute([
        ':doctor_id' => $doctorId,
        ':weekday' => $weekday,
        ':start_time' => $start,
        ':end_time' => $end,
    ]);
    if ($overlapStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'This session overlaps with an existing one']);
        exit;
    }


    try {
        $stmt = $pdo->prepare(
            'INSERT INTO doctor_availability (doctor_user_id, weekday, start_time, end_time)
             VALUES (:doctor_id, :weekday, :start_time, :end_time)'
        );
        $stmt->execute([
            ':doctor_id' => $doctorId,
            ':weekday' => $weekday,
            ':start_time' => $start,
            ':end_time' => $end,
        ]);


        $id = (int) $pdo->lastInsertId();
        write_audit_log('doctor_availability_added', 'doctor_availability', $id, [
            'doctor_id' => $doctorId,
            'weekday' => $weekday,
            'start_time' => $start,
            'end_time' => $end,
        ]);


        echo json_encode(['success' => true, 'id' => $id]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add availability']);
    }
    exit;
}


if ($action === 'delete') {
    $id = (int) ($payload['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['error' => 'Invalid availability id']);
        exit;
    }


    $stmt = $pdo->prepare('DELETE FROM doctor_availability WHERE id = :id AND doctor_user_id = :doctor_id');
    $stmt->execute([
        ':id' => $id,
        ':doctor_id' => $doctorId,
    ]);


    if ($stmt->rowCount() < 1) {
        http_response_code(404);
        echo json_encode(['error' => 'Availability record not found']);
        exit;
    }


    write_audit_log('doctor_availability_deleted', 'doctor_availability', $id, [
        'doctor_id' => $doctorId,
    ]);


    echo json_encode(['success' => true]);
    exit;
}


http_response_code(422);
echo json_encode(['error' => 'Invalid action']);



