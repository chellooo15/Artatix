<?php

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return rtrim($GLOBALS['config']['app']['url'], '/') . '/' . ltrim($path, '/');
}

function old(string $key, string $default = ''): string
{
    return $_SESSION['old'][$key] ?? $default;
}

function setOld(array $data): void
{
    $_SESSION['old'] = $data;
}

function clearOld(): void
{
    unset($_SESSION['old']);
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="_token" value="' . csrfToken() . '">';
}

function verifyCsrf(): bool
{
    $token = $_POST['_token'] ?? '';
    if (!$token) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_CSRFTOKEN'] ?? '';
    }
    // Fallback: getallheaders case-insensitive
    if (!$token && function_exists('getallheaders')) {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $token = $headers['x-csrf-token'] ?? $headers['x-csrftoken'] ?? '';
    }
    return $token && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function isCurrentPath(string $path): bool
{
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if ($path === '/') return $uri === '/';
    return $uri === $path || str_starts_with($uri, rtrim($path,'/').'/');
}

function formatRupiah(float|int $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function formatDate(string $date, string $format = 'd F Y'): string
{
    return date($format, strtotime($date));
}

function formatDateTime(string $datetime, string $format = 'd F Y H:i'): string
{
    return date($format, strtotime($datetime));
}

function classTypeLabel(string $type): string
{
    return match ($type) {
        'cat' => 'CAT (Duduk)',
        'standing' => 'Festival / Standing',
        'vip' => 'VIP',
        'vvip' => 'VVIP',
        default => ucfirst($type),
    };
}

function eventStatusLabel(string $status): string
{
    return match ($status) {
        'draft' => 'Draft',
        'po_open' => 'Flash Sale',
        'public' => 'For Sale',
        'sold_out' => 'Sold Out',
        'cancelled' => 'Dibatalkan',
        default => ucfirst($status),
    };
}

function orderStatusLabel(string $status): string
{
    return match ($status) {
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Lunas',
        'cancelled' => 'Dibatalkan',
        'expired' => 'Kedaluwarsa',
        default => ucfirst($status),
    };
}

function paymentStatusLabel(string $status): string
{
    return match ($status) {
        'pending' => 'Menunggu Verifikasi',
        'approved' => 'Diverifikasi',
        'rejected' => 'Ditolak',
        default => ucfirst($status),
    };
}

function generateTicketCode(): string
{
    return 'ATX-' . strtoupper(bin2hex(random_bytes(6)));
}

function generatePOCode(): string
{
    return 'FS-' . strtoupper(bin2hex(random_bytes(4)));
}

function eventBannerUrl(?string $filename): ?string
{
    if (!$filename) return null;
    $filename = ltrim($filename, '/');
    if (str_contains($filename, '..')) return null;
    $disk = rtrim($GLOBALS['config']['upload']['path'] ?? __DIR__ . '/../assets/uploads', '/') . '/' . $filename;
    if (!is_file($disk)) return null;
    return asset('assets/uploads/' . $filename);
}

function h(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function e(mixed $value): string
{
    return h((string)$value);
}

function dd(mixed ...$vars): void
{
    foreach ($vars as $var) {
        var_dump($var);
    }
    die();
}