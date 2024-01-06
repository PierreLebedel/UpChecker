<?php

namespace App\Models;

use App\Enums\BehaviorActionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'behavior_id',
        'position',
        'type',
    ];

    protected $casts = [
        'position'          => 'integer',
        'type'              => BehaviorActionEnum::class,
    ];

    public function behavior(): BelongsTo
    {
        return $this->belongsTo(Behavior::class);
    }
}
