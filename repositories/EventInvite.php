<?php

namespace App\Repositories;

class EventInvite extends Model
{
    protected static string $table = 'event_invites';
    protected static array $fillable = [
        'event_id', 'token', 'can_edit_info', 'max_uses', 'used_count',
        'expires_at', 'created_by', 'is_active'
    ];

    public static function findByToken(string $token): ?static
    {
        return static::first(['token' => $token]);
    }

    public static function forEvent(int $eventId): array
    {
        return static::all(['event_id' => $eventId], 'created_at DESC');
    }

    public function isUsable(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && strtotime($this->expires_at) < time()) return false;
        if ((int)$this->used_count >= (int)$this->max_uses) return false;
        return true;
    }

    public function unusableReason(): string
    {
        if (!$this->is_active) return 'Link undangan sudah dinonaktifkan';
        if ($this->expires_at && strtotime($this->expires_at) < time()) return 'Link undangan sudah kedaluwarsa';
        if ((int)$this->used_count >= (int)$this->max_uses) return 'Kuota link undangan sudah habis';
        return 'Link undangan tidak valid';
    }

    public function event(): ?Event
    {
        return Event::find($this->event_id);
    }
}
