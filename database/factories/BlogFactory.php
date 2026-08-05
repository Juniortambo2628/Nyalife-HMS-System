<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.uniqid(),
            'excerpt' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(5, true),
            'image_path' => $this->faker->optional()->imageUrl(800, 400),
            'author_id' => User::factory(),
            'tags' => $this->faker->optional()->randomElement([
                null,
                json_encode(['health', 'pregnancy', 'women']),
                json_encode(['medical', 'advice', 'tips']),
                json_encode(['clinic', 'services', 'obstetrics']),
            ]),
            'is_published' => $this->faker->boolean(80),
            'published_at' => $this->faker->optional()->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
