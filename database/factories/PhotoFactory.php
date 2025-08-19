<?php

namespace Feeldee\Framework\Database\Factories;

use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\PhotoType;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Photo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo_type' => fake()->randomElement(PhotoType::cases()),
            'src' => fake()->url(),
            'posted_at' => fake()->dateTime(),
        ];
    }
}
