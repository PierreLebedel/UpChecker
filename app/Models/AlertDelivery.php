<?php

namespace App\Models;

use App\Enums\AlertChannel;
use App\Enums\AlertDeliveryStatus;
use Database\Factories\AlertDeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $monitor_id
 * @property string $check_result_id
 * @property AlertChannel $channel
 * @property string $recipient
 * @property AlertDeliveryStatus $status
 * @property Carbon|null $sent_at
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'monitor_id',
    'check_result_id',
    'channel',
    'recipient',
    'status',
    'sent_at',
    'error_message',
])]
class AlertDelivery extends Model
{
    /** @use HasFactory<AlertDeliveryFactory> */
    use HasFactory, HasUuids;

    /**
     * @return BelongsTo<Monitor, $this>
     */
    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    /**
     * @return BelongsTo<CheckResult, $this>
     */
    public function checkResult(): BelongsTo
    {
        return $this->belongsTo(CheckResult::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => AlertChannel::class,
            'status' => AlertDeliveryStatus::class,
            'sent_at' => 'datetime',
        ];
    }
}
