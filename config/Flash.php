<?php

namespace App\Config;

class Flash
{
    public static function set(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    public static function get(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        if ($message) {
            unset($_SESSION['flash'][$type]);
        }
        return $message;
    }

    public static function getAll(): array
    {
        $flashes = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $flashes;
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    public static function warning(string $message): void
    {
        self::set('warning', $message);
    }

    public static function info(string $message): void
    {
        self::set('info', $message);
    }
}