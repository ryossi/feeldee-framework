<?php

namespace Feeldee\Framework\Database\Factories;

use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Journal;
use Feeldee\Framework\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;


class TagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Tag::class;

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
                Journal::type(),
                Photo::type(),
                Item::type(),
                Location::type(),
            ]),
        ];
    }
}
