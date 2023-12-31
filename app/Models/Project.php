<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'user_id',
        'name',
    ];

    protected $casts = [
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            if (! $model->slug || Str::of($model->slug)->length() != 12) {
                $model->slug = Str::random(12);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function endpoints(): HasMany
    {
        return $this->hasMany(Endpoint::class);
    }
}
