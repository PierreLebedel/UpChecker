<?php

namespace App\Models;

use App\Enums\AccountTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'params',
    ];

    protected $casts = [
        'type' => AccountTypeEnum::class,
        'params' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->type->getInstance($this)->getAccountName(),
        );
    }

}
