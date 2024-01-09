<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => 'https://ui-avatars.com/api/?name='.$this->name.'&background=ff52d8&color=050618&size=128',
        );
    }
}
