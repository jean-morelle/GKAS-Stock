<?php

namespace Database\Factories;

use App\Enums\ProductUnit;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(3, true),
            'reference' => fake()->unique()->bothify('REF-####'),
            'description' => null,
            'unit' => ProductUnit::Unit,
            'stock_quantity' => 0,
            'alert_threshold' => 10,
        ];
    }

    /**
     * Stock initial pour les tests (hors mouvement, à n'utiliser qu'en test).
     */
    public function withStock(int $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => $quantity,
        ]);
    }
}
