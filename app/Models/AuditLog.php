<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AuditLogActions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'meta',
        'occurred_at',
    ];

    public function casts(): array
    {
        return [
            'action' => AuditLogActions::class,
            'occurred_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
