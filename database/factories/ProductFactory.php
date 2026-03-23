<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    private const CATEGORIES = [
        'Electronics',
        'Clothing',
        'Home & Kitchen',
        'Sports & Outdoors',
        'Books',
        'Toys & Games',
        'Health & Beauty',
        'Automotive',
        'Office Supplies',
        'Food & Beverages',
    ];

    public function definition(): array
    {
        return [
            'name'           => fake()->unique()->words(rand(2, 4), true),
            'price'          => fake()->randomFloat(2, 1, 999.99),
            'category'       => fake()->randomElement(self::CATEGORIES),
            'stock_quantity' => fake()->numberBetween(0, 150),
        ];
    }

    /** Product explicitly out of stock. */
    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => 0]);
    }

    /** Product with low stock (1-10). */
    public function lowStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => fake()->numberBetween(1, 10)]);
    }

    /** Product with healthy stock (> 10). */
    public function inStock(): static
    {
        return $this->state(fn () => ['stock_quantity' => fake()->numberBetween(11, 150)]);
    }
}
