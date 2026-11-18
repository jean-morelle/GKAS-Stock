<?php

namespace App\Models;

use App\Enums\ProductUnit;
use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * stock_quantity est exclu : le stock ne peut évoluer
     * que via StockService (entrées / sorties).
     */
    protected $fillable = [
        'category_id',
        'name',
        'reference',
        'description',
        'unit',
        'alert_threshold',
    ];

    protected $attributes = [
        'stock_quantity' => 0,
        'alert_threshold' => 0,
    ];

    protected function casts(): array
    {
        return [
            'unit' => ProductUnit::class,
            'stock_quantity' => 'integer',
            'alert_threshold' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stockEntries(): HasMany
    {
        return $this->hasMany(StockEntry::class);
    }

    public function stockExits(): HasMany
    {
        return $this->hasMany(StockExit::class);
    }

    public function hasMovements(): bool
    {
        return $this->stockEntries()->exists() || $this->stockExits()->exists();
    }

    /**
     * Rupture : stock = 0. Stock faible : stock <= seuil.
     */
    public function stockStatus(): StockStatus
    {
        return match (true) {
            $this->stock_quantity === 0 => StockStatus::OutOfStock,
            $this->stock_quantity <= $this->alert_threshold => StockStatus::Low,
            default => StockStatus::Available,
        };
    }

    /**
     * Produits nécessitant une attention (stock <= seuil, ruptures incluses).
     */
    public function scopeNeedsAttention(Builder $query): void
    {
        $query->whereColumn('stock_quantity', '<=', 'alert_threshold');
    }

    /**
     * Stock faible hors rupture (0 < stock <= seuil).
     */
    public function scopeLowStock(Builder $query): void
    {
        $query->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'alert_threshold');
    }

    public function scopeOutOfStock(Builder $query): void
    {
        $query->where('stock_quantity', 0);
    }

    public function scopeSearch(Builder $query, ?string $term): void
    {
        if (blank($term)) {
            return;
        }

        $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('reference', 'like', "%{$term}%");
        });
    }

    /**
     * Quantité formatée avec l'unité du produit (ex. « 450 m »).
     */
    public function formatQuantity(int $quantity): string
    {
        return $this->unit->format($quantity);
    }
}
