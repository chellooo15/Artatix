<?php

namespace App\Middleware;

use App\Database\Database;
use App\Config\Flash;

class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }
        
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            self::logout();
            return null;
        }
        
        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }

    public static function isOrganizer(): bool
    {
        $user = self::user();
        return $user && $user['role'] === 'organizer';
    }

    public static function isAdminOrOrganizer(): bool
    {
        return self::isAdmin() || self::isOrganizer();
    }

    public static function organizerEventIds(): array
    {
        $user = self::user();
        if (!$user) return [];
        return \App\Repositories\EventOrganizer::eventIdsForUser((int)$user['id']);
    }

    public static function canManageEvent(int $eventId): bool
    {
        if (self::isAdmin()) return true;
        $user = self::user();
        if (!$user || $user['role'] !== 'organizer') return false;
        return \App\Repositories\EventOrganizer::isOrganizer($eventId, (int)$user['id']);
    }

    public static function canEditEventInfo(int $eventId): bool
    {
        if (self::isAdmin()) return true;
        $user = self::user();
        if (!$user || $user['role'] !== 'organizer') return false;
        return \App\Repositories\EventOrganizer::canEditInfo($eventId, (int)$user['id']);
    }

    public static function login(array $user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }

    public static function requireAuth(): void
    {
        if (self::guest()) {
            Flash::set('error', 'Silakan login terlebih dahulu');
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!self::isAdmin()) {
            Flash::set('error', 'Akses ditolak');
            header('Location: /');
            exit;
        }
    }

    public static function requireAdminOrOrganizer(): void
    {
        self::requireAuth();
        if (!self::isAdminOrOrganizer()) {
            Flash::set('error', 'Akses ditolak');
            header('Location: /');
            exit;
        }
    }

    public static function requireGuest(): void
    {
        if (self::check()) {
            header('Location: /');
            exit;
        }
    }
}