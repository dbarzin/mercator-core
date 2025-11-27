<?php

namespace Mercator\Core\Factories;

use Mercator\Core\Models\MApplicationEvent;
use Mercator\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MApplicationEventFactory extends Factory
{
    protected $model = MApplicationEvent::class;

    public function definition(): array
    {
        return [
            'message' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
