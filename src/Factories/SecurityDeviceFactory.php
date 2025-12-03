<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\SecurityDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SecurityDeviceFactory extends Factory
{
    protected $model = SecurityDevice::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'address_ip' => $this->faker->ipv4(),
            'vendor' => $this->faker->word(),
            'product' => $this->faker->word(),
            'version' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
