<?php

namespace Feeldee\Framework\Database\Factories;

use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Post;
use Feeldee\Framework\Models\Recorder;
use Illuminate\Database\Eloquent\Factories\Factory;


class RecorderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Recorder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'type' => $this->faker->randomElement([
                Post::type(),
                Photo::type(),
                Item::type(),
                Location::type(),
            ]),
            'data_type' => $this->faker->randomElement([
                'int',
                'float',
                'string',
            ]),
        ];
    }
}
