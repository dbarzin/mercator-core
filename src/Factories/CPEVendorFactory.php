<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\CPEVendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class CPEVendorFactory extends Factory
{
    protected $model = CPEVendor::class;

    public function definition(): array
    {
        return [
            'part' => $this->faker->word(),
            'name' => $this->faker->name(),
        ];
    }
}
