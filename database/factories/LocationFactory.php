<?php

namespace Feeldee\Framework\Database\Factories;

use Feeldee\Framework\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->title(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'zoom' => fake()->numberBetween(1, 10),
        ];
    }
}
