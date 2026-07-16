<?php

namespace App\Models;

use App\Enums\CheckStatus;
use Database\Factories\CheckResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $monitor_id
 * @property CheckStatus $status
 * @property int|null $http_status
 * @property int|null $response_time_ms
 * @property string|null $error_message
 * @property string $checked_url
 * @property Carbon $checked_at
 * @property string|null $response_excerpt
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'monitor_id',
    'status',
    'http_status',
    'response_time_ms',
    'error_message',
    'checked_url',
    'checked_at',
    'response_excerpt',
])]
class CheckResult extends Model
{
    /** @use HasFactory<CheckResultFactory> */
    use HasFactory, HasUuids;

    /**
     * @return BelongsTo<Monitor, $this>
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CheckStatus::class,
            'http_status' => 'integer',
            'response_time_ms' => 'integer',
            'checked_at' => 'datetime',
        ];
    }
}
