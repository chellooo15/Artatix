<?php

namespace App\Repositories;

use App\Database\Database;

class TicketClass extends Model
{
    protected static string $table = 'ticket_classes';
    protected static array $fillable = [
        'event_id', 'name', 'class_type', 'section', 'price',
        'description', 'benefits', 'capacity', 'sold_count', 'is_active',
        'seat_mode', 'seat_layout'
    ];
    
    public function event(): ?Event
    {
        return Event::find($this->event_id);
    }
    
    public function available(): int
    {
        return max(0, $this->capacity - $this->sold_count);
    }
    
    public function isSoldOut(): bool
    {
        return $this->available() <= 0;
    }
    
    public function incrementSold(int $qty = 1): bool
    {
        $this->sold_count += $qty;
        return $this->update(['sold_count' => $this->sold_count]);
    }
    
    public function decrementSold(int $qty = 1): bool
    {
        $this->sold_count = max(0, $this->sold_count - $qty);
        return $this->update(['sold_count' => $this->sold_count]);
    }
    
    public function getTypeLabel(): string
    {
        return classTypeLabel($this->class_type);
    }

    public function isSeated(): bool
    {
        return ($this->seat_mode ?? 'quantity') === 'seated';
    }

    public function seatLayout(): array
    {
        $raw = $this->seat_layout ?? null;
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($raw)) $raw = [];
        return [
            'rows' => array_values(array_filter(array_map('strval', (array)($raw['rows'] ?? [])))),
            'cols' => max(0, (int)($raw['cols'] ?? 0)),
            'blocked' => array_values(array_filter(array_map('strval', (array)($raw['blocked'] ?? [])))),
            'prefix' => (string)($raw['prefix'] ?? ''),
        ];
    }

    public static function buildLayout(array $rows, int $cols, array $blocked = [], string $prefix = ''): array
    {
        $rows = array_values(array_filter(array_map(fn($r) => strtoupper(trim((string)$r)), $rows)));
        $blocked = array_values(array_filter(array_map(fn($b) => strtoupper(trim((string)$b)), $blocked)));
        return ['rows' => $rows, 'cols' => max(0, $cols), 'blocked' => $blocked, 'prefix' => trim($prefix)];
    }

    public static function expandSeats(array $layout): array
    {
        $seats = [];
        foreach (($layout['rows'] ?? []) as $row) {
            for ($c = 1; $c <= (int)($layout['cols'] ?? 0); $c++) {
                $label = ($layout['prefix'] ?? '') . $row . $c;
                if (in_array($label, $layout['blocked'] ?? [], true)) continue;
                $seats[] = $label;
            }
        }
        return $seats;
    }

    public function seats(): array
    {
        if (!$this->isSeated()) return [];
        return static::expandSeats($this->seatLayout());
    }

    public function soldSeats(): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT t.seat_label FROM tickets t JOIN order_items oi ON t.order_item_id = oi.id JOIN orders o ON t.order_id = o.id WHERE oi.ticket_class_id = ? AND o.status IN ('pending', 'paid') AND t.seat_label IS NOT NULL");
        $stmt->execute([$this->id]);
        return array_values(array_filter(array_column($stmt->fetchAll(), 'seat_label')));
    }

    public function availableSeats(): array
    {
        if (!$this->isSeated()) return [];
        return array_values(array_diff($this->seats(), $this->soldSeats()));
    }
}