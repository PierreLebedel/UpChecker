<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkup extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint_id',
        'started_at',
        'microtime',
        'url',
        'status_code',
        'exception_message',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'microtime'  => 'float',
    ];

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(Endpoint::class);
    }
}
