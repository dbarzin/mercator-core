<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\Bay;
use Mercator\Core\Models\Building;
use Mercator\Core\Models\PhysicalSecurityDevice;
use Mercator\Core\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PhysicalSecurityDeviceFactory extends Factory
{
    protected $model = PhysicalSecurityDevice::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'type' => $this->faker->word(),
            'description' => $this->faker->text(),
            'address_ip' => $this->faker->ipv4(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'site_id' => Site::factory(),
            'building_id' => Building::factory(),
            'bay_id' => Bay::factory(),
        ];
    }
}
