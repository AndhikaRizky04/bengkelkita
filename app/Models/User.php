<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isMechanic()
    {
        return $this->hasRole('mekanik');
    }

    public function isKasir()
    {
        return $this->hasRole('kasir');
    }

    public function isCuci()
    {
        return $this->hasRole('cuci');
    }

    public function hasRole($roleName)
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Route name of the dashboard this user should land on, based on role.
     */
    public function dashboardRoute()
    {
        if ($this->isMechanic()) {
            return 'mechanic.dashboard';
        }
        if ($this->isCuci()) {
            return 'washer.dashboard';
        }
        return 'dashboard';
    }
}
