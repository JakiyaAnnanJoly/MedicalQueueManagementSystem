<?php


declare(strict_types=1);


header('Content-Type: application/json');


require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';
require __DIR__ . '/../audit.php';


require_roles_json(['admin']);


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}


$scenarios = require __DIR__ . '/../data/demo_scenarios.php';
$pdo = db();
$inserted = 0;
$skipped = 0;


try {
    $pdo->beginTransaction();


    foreach ($scenarios as $s) {
        $checkSlot = $pdo->prepare('SELECT id FROM appointments WHERE appointment_date = :d AND appointment_time = :t AND location_name = :l LIMIT 1');
        $checkSlot->execute([
            ':d' => $s['date'],
            ':t' => $s['time'] . ':00',
            ':l' => $s['location'],
        ]);
        if ($checkSlot->fetch()) {
            $skipped++;
            continue;
        }


        $patientStmt = $pdo->prepare('SELECT id FROM patients WHERE email = :e AND phone = :p LIMIT 1');
        $patientStmt->execute([':e' => $s['email'], ':p' => $s['phone']]);
        $patient = $patientStmt->fetch();


        if ($patient) {
            $patientId = (int) $patient['id'];
            $updatePatient = $pdo->prepare('UPDATE patients SET full_name = :n WHERE id = :id');
            $updatePatient->execute([':n' => $s['full_name'], ':id' => $patientId]);
        } else {
            $insertPatient = $pdo->prepare('INSERT INTO patients (full_name, email, phone) VALUES (:n, :e, :p)');
            $insertPatient->execute([
                ':n' => $s['full_name'],
                ':e' => $s['email'],
                ':p' => $s['phone'],
            ]);
            $patientId = (int) $pdo->lastInsertId();
        }


        $insertAppointment = $pdo->prepare(
            'INSERT INTO appointments
             (patient_id, service_name, location_name, doctor_name, appointment_date, appointment_time, ai_priority_score, symptoms, notes, status)
             VALUES
             (:pid, :service, :location, :doctor, :adate, :atime, :priority, :symptoms, :notes, "scheduled")'
        );
        $insertAppointment->execute([
            ':pid' => $patientId,
            ':service' => $s['service'],
            ':location' => $s['location'],
            ':doctor' => $s['doctor'],
            ':adate' => $s['date'],
            ':atime' => $s['time'] . ':00',
            ':priority' => $s['ai_priority_score'],
            ':symptoms' => $s['symptoms'],
            ':notes' => 'Seeded demo scenario: ' . $s['title'],
        ]);
        $inserted++;
    }


    $pdo->commit();


    write_audit_log('demo_seed_executed', 'system', null, [
        'inserted' => $inserted,
        'skipped' => $skipped,
    ]);


    echo json_encode([
        'success' => true,
        'inserted' => $inserted,
        'skipped' => $skipped,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Failed to seed demo data']);
}



