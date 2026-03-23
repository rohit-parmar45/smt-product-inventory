<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'category',
        'stock_quantity',
    ];

    protected $appends = ['stock_status'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    /**
     * Dynamically derived stock status.
     * stock > 10 → in-stock | stock 1–10 → low-stock | stock = 0 → out-of-stock
     */
    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->stock_quantity > 10) {
                    return 'in-stock';
                }

                if ($this->stock_quantity >= 1) {
                    return 'low-stock';
                }

                return 'out-of-stock';
            }
        );
    }

    /* ----------------------------------------------------------------
     | Query Scopes
     | ---------------------------------------------------------------- */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where('name', 'LIKE', "%{$term}%");
    }

    public function scopeFilterByCategory(Builder $query, ?string $category): Builder
    {
        if (! $category) {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function scopeFilterByStockStatus(Builder $query, ?string $status): Builder
    {
        if (! $status) {
            return $query;
        }

        return match ($status) {
            'in-stock'     => $query->where('stock_quantity', '>', 10),
            'low-stock'    => $query->whereBetween('stock_quantity', [1, 10]),
            'out-of-stock' => $query->where('stock_quantity', 0),
            default        => $query,
        };
    }

    public function scopeApplySorting(Builder $query, ?string $sortBy, ?string $sortDir): Builder
    {
        $allowed = ['price', 'created_at', 'name', 'stock_quantity'];
        $sortBy  = in_array($sortBy, $allowed) ? $sortBy : 'created_at';
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir);
    }
}
