<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../db.php';
require __DIR__ . '/../auth.php';

require_roles_json(['admin']);

$limit = (int) ($_GET['limit'] ?? 50);
$limit = max(1, min(200, $limit));

$stmt = db()->prepare(
    'SELECT a.id, a.action, a.entity_type, a.entity_id, a.details_json, a.created_at,
            u.username AS actor_username, u.full_name AS actor_name
     FROM audit_logs a
     LEFT JOIN users u ON u.id = a.actor_user_id
     ORDER BY a.id DESC
     LIMIT :lim'
);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

echo json_encode(['logs' => $rows]);
