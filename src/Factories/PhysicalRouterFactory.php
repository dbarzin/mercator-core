<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\Bay;
use Mercator\Core\Models\Building;
use Mercator\Core\Models\PhysicalRouter;
use Mercator\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PhysicalRouterFactory extends Factory
{
    protected $model = PhysicalRouter::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'vendor' => $this->faker->word(),
            'product' => $this->faker->word(),
            'version' => $this->faker->word(),
            'type' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'site_id' => Site::factory(),
            'building_id' => Building::factory(),
            'bay_id' => Bay::factory(),
        ];
    }
}
