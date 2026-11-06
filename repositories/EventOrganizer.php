<?php

namespace App\Repositories;

use App\Database\Database;

class EventOrganizer extends Model
{
    protected static string $table = 'event_organizers';
    protected static array $fillable = ['event_id', 'user_id', 'can_edit_info'];

    public static function isOrganizer(int $eventId, int $userId): bool
    {
        return static::first(['event_id' => $eventId, 'user_id' => $userId]) !== null;
    }

    public static function canEditInfo(int $eventId, int $userId): bool
    {
        $row = static::first(['event_id' => $eventId, 'user_id' => $userId]);
        return $row !== null && (int)($row->can_edit_info ?? 0) === 1;
    }

    public static function eventIdsForUser(int $userId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT event_id FROM event_organizers WHERE user_id = ?");
        $stmt->execute([$userId]);
        return array_map(fn($r) => (int)$r['event_id'], $stmt->fetchAll());
    }

    public static function add(int $eventId, int $userId, bool $canEditInfo = false): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO event_organizers (event_id, user_id, can_edit_info) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE can_edit_info = VALUES(can_edit_info)");
        $stmt->execute([$eventId, $userId, $canEditInfo ? 1 : 0]);
    }

    public static function remove(int $eventId, int $userId): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM event_organizers WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eventId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function setCanEdit(int $eventId, int $userId, bool $canEditInfo): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE event_organizers SET can_edit_info = ? WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$canEditInfo ? 1 : 0, $eventId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public static function forEvent(int $eventId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT u.id, u.name, u.email, eo.can_edit_info, eo.created_at FROM event_organizers eo JOIN users u ON eo.user_id = u.id WHERE eo.event_id = ? ORDER BY u.name ASC");
        $stmt->execute([$eventId]);
        return $stmt->fetchAll();
    }
}
