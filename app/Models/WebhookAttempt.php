<?php

namespace App\Models;

use App\Enums\HttpStatus;
use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'status',
        'http_code',
        'response_time',
        'error',
        'occurred_at',
    ];

    public function casts(): array
    {
        return [
            'status' => WebhookStatus::class,
            'http_code' => HttpStatus::class,
            'response_time' => 'integer',
            'occurred_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Webhook, $this>
     */
    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
