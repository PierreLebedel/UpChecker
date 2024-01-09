<?php

namespace App\Models;

use App\Enums\BehaviorActionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Action extends Model
{
    use HasFactory;

    protected $fillable = [
        'behavior_id',
        'account_id',
        'position',
        'type',
        'params',
    ];

    protected $casts = [
        'position' => 'integer',
        'type'     => BehaviorActionEnum::class,
        'params'   => 'array',
    ];

    public function behavior(): BelongsTo
    {
        return $this->belongsTo(Behavior::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
