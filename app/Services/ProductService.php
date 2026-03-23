<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    /**
     * Get paginated products with filtering, searching, and sorting.
     */
    public function getProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->search($filters['search'] ?? null)
            ->filterByCategory($filters['category'] ?? null)
            ->filterByStockStatus($filters['stock_status'] ?? null)
            ->applySorting(
                $filters['sort_by'] ?? 'created_at',
                $filters['sort_dir'] ?? 'desc'
            );

        $perPage = min((int) ($filters['per_page'] ?? 15), 100);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new product.
     */
    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update an existing product.
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Get distinct categories for filter dropdowns.
     */
    public function getCategories(): array
    {
        return Product::distinct()->pluck('category')->sort()->values()->toArray();
    }
}
