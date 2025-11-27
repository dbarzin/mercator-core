<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\ForestAd;
use Mercator\Core\Models\ZoneAdmin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ForestAdFactory extends Factory
{
    protected $model = ForestAd::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'zone_admin_id' => ZoneAdmin::factory(),
        ];
    }
}
