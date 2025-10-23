<?php

namespace Feeldee\Framework\Database\Factories;

use Feeldee\Framework\Models\Record;
use Illuminate\Database\Eloquent\Factories\Factory;


class RecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Record::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'value' => $this->faker->randomNumber(),
        ];
    }
}
