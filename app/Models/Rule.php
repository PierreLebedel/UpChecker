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
        'compare_field',
        'compare_sign',
        'compare_value',
    ];

    protected $casts = [
        'position'          => 'integer',
        'compare_field'    => BehaviorRuleEnum::class,
    ];

    public function behavior(): BelongsTo
    {
        return $this->belongsTo(Behavior::class);
    }
}
