<?php

namespace App\Repositories;

class POAccess extends Model
{
    protected static string $table = 'po_accesses';
    protected static array $fillable = [
        'event_id', 'code', 'max_quota', 'used_count', 'expires_at', 'created_by', 'is_active'
    ];
    
    public function event(): ?Event
    {
        return Event::find($this->event_id);
    }
    
    public function creator(): ?User
    {
        return User::find($this->created_by);
    }
    
    public function remaining(): int
    {
        return max(0, $this->max_quota - $this->used_count);
    }
    
    public function isExpired(): bool
    {
        return $this->expires_at && strtotime($this->expires_at) < time();
    }
    
    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired() && $this->remaining() > 0;
    }
}