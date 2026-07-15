<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        $types = ['text', 'textarea', 'number', 'email', 'url', 'boolean', 'json'];
        $groups = ['general', 'contact', 'social', 'seo', 'appearance', 'billing', 'notifications', 'system'];

        $key = $this->faker->unique()->word() . '_' . $this->faker->word();

        return [
            'key' => $key,
            'value' => $this->faker->sentence(),
            'type' => $this->faker->randomElement($types),
            'group' => $this->faker->randomElement($groups),
            'label' => ucwords(str_replace('_', ' ', $key)),
        ];
    }

    public function text(string $key, string $value = null, string $group = 'general'): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
            'value' => $value ?? $this->faker->sentence(),
            'type' => 'text',
            'group' => $group,
        ]);
    }

    public function boolean(string $key, bool $value = false, string $group = 'general'): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
            'value' => $value ? '1' : '0',
            'type' => 'boolean',
            'group' => $group,
        ]);
    }

    public function json(string $key, array $value = null, string $group = 'general'): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key,
            'value' => $value ? json_encode($value) : json_encode(['enabled' => true]),
            'type' => 'json',
            'group' => $group,
        ]);
    }
}