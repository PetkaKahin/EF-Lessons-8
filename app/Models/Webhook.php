<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $url
 * @property string $secret
 * @property bool $enabled
 */
class Webhook extends Model
{
    protected $fillable = [
        'url',
        'secret',
        'enabled',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'bool',
        ];
    }

    /**
     * @return HasMany<WebhookAttempt, $this>
     */
    public function webhookAttempts(): HasMany
    {
        return $this->hasMany(WebhookAttempt::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function webhookable(): MorphTo
    {
        return $this->morphTo();
    }
}
