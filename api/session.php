<?php

declare(strict_types=1);

header('Content-Type: application/json');

require __DIR__ . '/../auth.php';

require_login_json();

echo json_encode(['user' => current_user()]);
