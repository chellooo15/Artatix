<?php

namespace App\Repositories;

class Order extends Model
{
    protected static string $table = 'orders';
    protected static array $fillable = [
        'user_id', 'event_id', 'status', 'total_amount',
        'payment_method', 'payment_proof', 'expires_at',
        'is_po', 'po_code_used'
    ];
    
    public function user(): ?User
    {
        return User::find($this->user_id);
    }
    
    public function event(): ?Event
    {
        return Event::find($this->event_id);
    }
    
    public function items(): array
    {
        return OrderItem::all(['order_id' => $this->id]);
    }
    
    public function tickets(): array
    {
        return Ticket::all(['order_id' => $this->id]);
    }
    
    public function payment(): ?Payment
    {
        return Payment::first(['order_id' => $this->id]);
    }
    
    public function getStatusLabel(): string
    {
        return orderStatusLabel($this->status);
    }
    
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
    
    public function canPay(): bool
    {
        return $this->status === 'pending' && (!$this->expires_at || strtotime($this->expires_at) > time());
    }
    
    public function markPaid(): bool
    {
        $this->status = 'paid';
        $result = $this->update(['status' => 'paid']);
        
        if ($result) {
            foreach ($this->items() as $item) {
                $tc = TicketClass::find($item->ticket_class_id);
                if ($tc) $tc->incrementSold($item->quantity);
            }
        }
        
        return $result;
    }
    
    public function cancel(): bool
    {
        if ($this->status === 'paid') return false;
        
        $this->status = 'cancelled';
        return $this->update(['status' => 'cancelled']);
    }
}