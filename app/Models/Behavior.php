<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Behavior extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint_id',
        'slug',
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

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(Endpoint::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class)->orderBy('position');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class)->orderBy('position');
    }
}
