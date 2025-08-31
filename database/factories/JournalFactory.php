<?php

namespace Feeldee\Framework\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Feeldee\Framework\Models\Journal;

class JournalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Journal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'posted_at' => fake()->date(),
            'title' => fake()->title(),
        ];
    }
}
