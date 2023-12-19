<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    ];

    public static function boot() {
        parent::boot();
    
        static::creating(function (Model $model) {
            if( !$model->slug || Str::of($model->slug)->length() != 20 ){
                $model->slug = Str::random(20);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    


}
