<?php

namespace JobMetric\Extension\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JobMetric\Extension\Models\Extension;

/**
 * @extends Factory<Extension>
 */
class ExtensionFactory extends Factory
{
    protected $model = Extension::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver'  => $this->faker->word,
            'name'    => $this->faker->word,
            'info'    => null,
            'options' => null,
            'status'  => $this->faker->boolean,
        ];
    }

    /**
     * set driver
     *
     * @param string $driver
     *
     * @return static
     */
    public function setDriver(string $driver): static
    {
        return $this->state(fn (array $attributes) => [
            'driver' => $driver,
        ]);
    }

    /**
     * set name
     *
     * @param string $name
     *
     * @return static
     */
    public function setName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    /**
     * set info
     *
     * @param array $info
     *
     * @return static
     */
    public function setInfo(array $info): static
    {
        return $this->state(fn (array $attributes) => [
            'info' => json_encode([
                'description' => $info['description'] ?? null,
                'version'     => $info['version'] ?? null,
                'author'      => $info['author'] ?? null,
                'email'       => $info['email'] ?? null,
                'website'     => $info['website'] ?? null,
                'fields'      => $info['fields'] ?? [],
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * set options
     *
     * @param array $options
     *
     * @return static
     */
    public function setOptions(array $options): static
    {
        return $this->state(fn (array $attributes) => [
            'options' => $options,
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
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
