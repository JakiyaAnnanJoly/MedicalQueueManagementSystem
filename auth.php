<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    $user = $_SESSION['user'] ?? null;
    return is_array($user) ? $user : null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function login_user(array $user): void
{
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'username' => (string) $user['username'],
        'full_name' => (string) $user['full_name'],
        'role' => (string) $user['role'],
        'profile_image_path' => (string) ($user['profile_image_path'] ?? ''),
    ];
}

function set_current_profile_image(string $path): void
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return;
    }
    $_SESSION['user']['profile_image_path'] = $path;
}

function set_current_full_name(string $name): void
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return;
    }
    $_SESSION['user']['full_name'] = $name;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function has_role(array $roles): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    return in_array($user['role'], $roles, true);
}

function require_login_page(): void
{
    if (is_logged_in()) {
        return;
    }
    header('Location: login.php');
    exit;
}

function require_roles_page(array $roles): void
{
    require_login_page();
    if (has_role($roles)) {
        return;
    }
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

function require_login_json(): void
{
    if (is_logged_in()) {
        return;
    }
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

function require_roles_json(array $roles): void
{
    require_login_json();
    if (has_role($roles)) {
        return;
    }
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden']);
    exit;
}
