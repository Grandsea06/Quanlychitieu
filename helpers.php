<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function money($amount): string
{
    return number_format((float)$amount, 0, ',', '.') . ' ₫';
}

function valid_month(string $month): string
{
    return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) ? $month : date('Y-m');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Yêu cầu không hợp lệ hoặc phiên làm việc đã hết hạn. Hãy quay lại và thử lại.');
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
