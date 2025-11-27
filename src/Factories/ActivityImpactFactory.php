<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\Activity;
use Mercator\Core\Models\ActivityImpact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ActivityImpactFactory extends Factory
{
    protected $model = ActivityImpact::class;

    public function definition(): array
    {
        return [
            'impact_type' => $this->faker->word(),
            'severity' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'activity_id' => Activity::factory(),
        ];
    }
}
