<?php

namespace App\Repositories;

class Payment extends Model
{
    protected static string $table = 'payments';
    protected static array $fillable = [
        'order_id', 'method', 'amount', 'proof_image', 'status', 'verified_by', 'verified_at'
    ];
    
    public function order(): ?Order
    {
        return Order::find($this->order_id);
    }
    
    public function verifier(): ?User
    {
        return User::find($this->verified_by);
    }
    
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
    
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
    
    public function getStatusLabel(): string
    {
        return paymentStatusLabel($this->status);
    }
}