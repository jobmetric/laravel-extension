<?php

namespace JobMetric\Extension\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Extension\Models\Plugin;

/**
 * @extends Factory<Plugin>
 */
class PluginFactory extends Factory
{
    protected $model = Plugin::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'extension_id' => null,
            'title' => $this->faker->word,
            'fields' => null,
            'status' => $this->faker->boolean
        ];
    }

    /**
     * set extension id
     *
     * @param int $extension_id
     *
     * @return static
     */
    public function setDriver(int $extension_id): static
    {
        return $this->state(fn(array $attributes) => [
            'extension_id' => $extension_id
        ]);
    }

    /**
     * set title
     *
     * @param string $title
     *
     * @return static
     */
    public function setName(string $title): static
    {
        return $this->state(fn(array $attributes) => [
            'title' => $title
        ]);
    }

    /**
     * set fields
     *
     * @param array $fields
     *
     * @return static
     */
    public function setFields(array $fields): static
    {
        return $this->state(fn(array $attributes) => [
            'fields' => $fields
        ]);
    }

    /**
     * set status
     *
     * @param bool $status
     *
     * @return static
     */
    public function setStatus(bool $status): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => $status
        ]);
    }
}
