<?php

namespace Feeldee\Framework\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Feeldee\Framework\Models\Post;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_date' => fake()->date(),
            'title' => fake()->title(),
        ];
    }
}
