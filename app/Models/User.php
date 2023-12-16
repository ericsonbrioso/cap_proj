<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;



class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    const ROLES = [
        self ::ROLE_ADMIN => 'Admin',
        self ::ROLE_USER => 'User',
    ];

    protected $fillable = [
        'name',
        'email',
        'contact',
        'address',
        'password',
        'role',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() || $this->isUser();
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser()
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    
}
