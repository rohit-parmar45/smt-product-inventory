<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Admin user ──────────────────────────────────────────
        User::factory()->admin()->create([
            'name'  => 'Admin User',
            'email' => 'admin@inventory.com',
        ]);

        // ── Regular user ────────────────────────────────────────
        User::factory()->create([
            'name'  => 'Regular User',
            'email' => 'user@inventory.com',
        ]);

        // ── Sample products (50 total: mix of stock statuses) ──
        Product::factory()->count(30)->inStock()->create();
        Product::factory()->count(12)->lowStock()->create();
        Product::factory()->count(8)->outOfStock()->create();
    }
}
