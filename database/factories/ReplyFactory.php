<?php

namespace Feeldee\Framework\Database\Factories;

use Feeldee\Framework\Models\Reply;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReplyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Reply::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'replied_at' => fake()->date(),
            'replyer_nickname' => fake()->name(),
        ];
    }
}
