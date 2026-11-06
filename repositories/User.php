<?php

namespace App\Repositories;

class User extends Model
{
    protected static string $table = 'users';
    protected static array $fillable = ['name', 'email', 'password_hash', 'phone', 'role'];
    
    public static function createUser(array $data): static
    {
        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);
        return static::create($data);
    }
    
    public static function findByEmail(string $email): ?static
    {
        return static::first(['email' => $email]);
    }
    
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password_hash);
    }
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOrganizer(): bool
    {
        return $this->role === 'organizer';
    }
}