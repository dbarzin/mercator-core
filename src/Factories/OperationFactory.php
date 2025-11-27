<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\Operation;
use Mercator\Core\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Mercator\Core\Models\Operation>
 */
class OperationFactory extends Factory
{
    protected $model = Operation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'process_id' => null,
        ];
    }

    /**
     * Operation avec un processus associé
     */
    public function withProcess(): static
    {
        return $this->state(fn (array $attributes) => [
            'process_id' => Process::factory(),
        ]);
    }
}
