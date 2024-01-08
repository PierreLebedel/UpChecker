<?php

namespace App\Models;

use App\Enums\BehaviorRuleEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rule extends Model
{
    use HasFactory;

    protected $fillable = [
        'behavior_id',
        'position',
        'type',
        'params',
    ];

    protected $casts = [
        'position' => 'integer',
        'type'     => BehaviorRuleEnum::class,
        'params'   => 'array',
    ];

    public function behavior(): BelongsTo
    {
        return $this->belongsTo(Behavior::class);
    }
}
