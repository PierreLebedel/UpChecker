<?php

namespace App\Models;

use App\Enums\MonitorCheckCriterionType;
use App\Enums\MonitorStatus;
use Database\Factories\MonitorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $name
 * @property string $url
 * @property bool $enabled
 * @property int $interval_minutes
 * @property int $timeout_seconds
 * @property array<int, array<string, mixed>> $check_criteria
 * @property MonitorStatus $current_status
 * @property Carbon|null $last_checked_at
 * @property Carbon|null $last_success_at
 * @property Carbon|null $last_failure_at
 * @property Carbon|null $last_alerted_at
 * @property Carbon|null $next_check_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'project_id',
    'name',
    'url',
    'enabled',
    'interval_minutes',
    'timeout_seconds',
    'check_criteria',
    'current_status',
    'last_checked_at',
    'last_success_at',
    'last_failure_at',
    'last_alerted_at',
    'next_check_at',
])]
class Monitor extends Model
{
    /** @use HasFactory<MonitorFactory> */
    use HasFactory, HasUuids;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'enabled' => true,
        'interval_minutes' => 5,
        'timeout_seconds' => 10,
        'check_criteria' => '[{"type":"http_status","expected":200}]',
        'current_status' => 'unknown',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function defaultCheckCriteria(): array
    {
        return [
            [
                'type' => MonitorCheckCriterionType::HttpStatus->value,
                'expected' => 200,
            ],
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<CheckResult, $this>
     */
    public function checkResults(): HasMany
    {
        return $this->hasMany(CheckResult::class);
    }

    /**
     * @return HasMany<AlertDelivery, $this>
     */
    public function alertDeliveries(): HasMany
    {
        return $this->hasMany(AlertDelivery::class);
    }

    /**
     * @param  Builder<Monitor>  $query
     * @return Builder<Monitor>
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query
            ->where('enabled', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('next_check_at')
                    ->orWhere('next_check_at', '<=', now());
            });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'interval_minutes' => 'integer',
            'timeout_seconds' => 'integer',
            'check_criteria' => 'array',
            'current_status' => MonitorStatus::class,
            'last_checked_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
            'last_alerted_at' => 'datetime',
            'next_check_at' => 'datetime',
        ];
    }
}
