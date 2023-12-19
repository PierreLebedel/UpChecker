<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'user_id',
        'name',
    ];

    public static function boot() {
        parent::boot();
    
        static::creating(function (Model $model) {
            if( !$model->slug || Str::of($model->slug)->length() != 20 ){
                $model->slug = Str::random(20);
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
