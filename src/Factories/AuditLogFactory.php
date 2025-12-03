<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\AuditLog;
use Mercator\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->text(),
            'subject_id' => $this->faker->randomNumber(),
            'subject_type' => $this->faker->word(),
            'properties' => $this->faker->word(),
            'host' => $this->faker->ipv4(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            // Sélectionne un user aléatoire, ou en crée un si la table est vide
            'user_id' => User::query()->inRandomOrder()->value('id')
                ?? User::factory(),
            ];
    }
}
