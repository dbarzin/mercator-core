<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\ApplicationModule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ApplicationModuleFactory extends Factory
{
    protected $model = ApplicationModule::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
