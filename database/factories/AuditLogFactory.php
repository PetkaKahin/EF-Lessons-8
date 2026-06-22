<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'entity_type' => $this->faker->word(),
            'entity_id' => $this->faker->uuid(),
            'action' => $this->faker->word(),
            'meta' => $this->faker->word(),
            'occurred_at' => now(),
        ];
    }
}
