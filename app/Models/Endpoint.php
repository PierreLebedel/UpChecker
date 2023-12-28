<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Endpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'project_id',
        'url',
        'expected_status_code',
        'timeout',
        'follow_redirects',
        'checkup_delay',
    ];

    protected $casts = [
        'timeout' => 'integer',
        'follow_redirects' => 'boolean',
        'checkup_delay' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            if (! $model->slug || Str::of($model->slug)->length() != 20) {
                $model->slug = Str::random(20);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function checkups(): HasMany
    {
        return $this->hasMany(Checkup::class);
    }

    public function lastCheckup()
    {
        return $this->hasOne(Checkup::class)->latest();
    }
}
