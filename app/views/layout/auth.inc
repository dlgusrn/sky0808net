<?php

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit;
    }
}

function require_csrf(): void
{
    $token = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(403);
        exit;
    }
}

function require_member(): void
{
    if (empty($_SESSION['username']) || empty($_SESSION['branch'])) {
        http_response_code(401);
        exit;
    }
}

function require_admin(?string $permission = null): array
{
    global $DB;

    if (empty($_SESSION['admin_username'])) {
        http_response_code(401);
        exit;
    }

    $statement = $DB->prepare('SELECT * FROM member_admin WHERE username = :username LIMIT 1');
    $statement->execute(['username' => $_SESSION['admin_username']]);
    $admin = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$admin || ($permission !== null && ($admin[$permission] ?? 'N') !== 'Y')) {
        http_response_code(403);
        exit;
    }

    return $admin;
}

function input_int(string $key, array $source): int
{
    $value = filter_var($source[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($value === false) {
        http_response_code(422);
        exit;
    }

    return $value;
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
