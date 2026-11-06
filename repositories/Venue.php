<?php

namespace App\Repositories;

class Venue extends Model
{
    protected static string $table = 'venues';
    protected static array $fillable = ['name', 'address', 'city', 'capacity', 'image'];
    
    public function events(): array
    {
        return Event::all(['venue_id' => $this->id]);
    }
}