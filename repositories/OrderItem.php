<?php

namespace App\Repositories;

class OrderItem extends Model
{
    protected static string $table = 'order_items';
    protected static array $fillable = ['order_id', 'ticket_class_id', 'quantity', 'unit_price', 'subtotal'];
    
    public function ticketClass(): ?TicketClass
    {
        return TicketClass::find($this->ticket_class_id);
    }
}