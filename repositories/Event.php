<?php

namespace App\Repositories;

use App\Database\Database;

class Event extends Model
{
    protected static string $table = 'events';
    protected static array $fillable = [
        'name', 'description', 'event_date', 'event_time', 'venue_id',
        'status', 'po_quota', 'po_open_at', 'public_open_at', 'banner_image'
    ];
    
    public function venue(): ?Venue
    {
        return Venue::find($this->venue_id);
    }
    
    public function ticketClasses(): array
    {
        return TicketClass::all(['event_id' => $this->id, 'is_active' => 1]);
    }
    
    public function getAvailableClasses(): array
    {
        return array_filter($this->ticketClasses(), fn($tc) => $tc->available() > 0);
    }
    
    public function isPOOpen(): bool
    {
        if ($this->status !== 'po_open' && $this->status !== 'public') return false;
        if (!$this->po_open_at) return false;
        return strtotime($this->po_open_at) <= time();
    }
    
    public function isPublicOpen(): bool
    {
        if ($this->status !== 'public') return false;
        if (!$this->public_open_at) return true;
        return strtotime($this->public_open_at) <= time();
    }
    
    public function canPurchase(string $classType = null, bool $isPO = false): bool
    {
        if ($this->status === 'cancelled' || $this->status === 'draft') return false;
        if ($this->status === 'sold_out') return false;
        
        if ($isPO) {
            return $this->isPOOpen();
        }
        
        return $this->isPublicOpen();
    }
    
    public function getPOUsedCount(): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM orders WHERE event_id = ? AND is_po = 1 AND status IN ('pending', 'paid')");
        $stmt->execute([$this->id]);
        return (int)($stmt->fetch()['cnt'] ?? 0);
    }
    
    public function getPORemaining(): int
    {
        return max(0, $this->po_quota - $this->getPOUsedCount());
    }

    public function organizers(): array
    {
        return EventOrganizer::forEvent((int)$this->id);
    }

    public function organizerCount(): int
    {
        return count($this->organizers());
    }

    public function isOrganizedBy(int $userId): bool
    {
        return EventOrganizer::isOrganizer((int)$this->id, $userId);
    }

    public function organizerCanEdit(int $userId): bool
    {
        return EventOrganizer::canEditInfo((int)$this->id, $userId);
    }
}