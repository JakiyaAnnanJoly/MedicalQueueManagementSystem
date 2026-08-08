<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function write_audit_log(string $action, string $entityType, ?int $entityId = null, array $details = []): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $actor = $_SESSION['user'] ?? null;
    $actorId = is_array($actor) ? (int) ($actor['id'] ?? 0) : 0;

    try {
        $stmt = db()->prepare(
            'INSERT INTO audit_logs (actor_user_id, action, entity_type, entity_id, details_json)
             VALUES (:actor, :action, :etype, :eid, :details)'
        );
        $stmt->execute([
            ':actor' => $actorId > 0 ? $actorId : null,
            ':action' => $action,
            ':etype' => $entityType,
            ':eid' => $entityId,
            ':details' => json_encode($details, JSON_UNESCAPED_UNICODE),
        ]);
    } catch (Throwable $e) {
        // Keep business flow uninterrupted if audit logging fails.
    }
}
