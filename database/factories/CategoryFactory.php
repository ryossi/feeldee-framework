<?php

namespace Feeldee\Framework\Database\Factories;

use Feeldee\Framework\Models\Category;
use Feeldee\Framework\Models\Item;
use Feeldee\Framework\Models\Location;
use Feeldee\Framework\Models\Photo;
use Feeldee\Framework\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;


class CategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Category::class;

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
        ];
    }

    public function withChildren($count = 1, array $attributes = [])
    {
        return $this->afterCreating(function (Category $category) use ($count, $attributes) {
            Category::factory()->count($count)->create(array_merge([
                'parent_id' => $category->id,
            ], $attributes));
        });
    }
}
