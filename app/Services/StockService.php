<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockExit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Point d'entrée unique pour toute modification du stock.
 * Chaque variation de stock est accompagnée d'un mouvement enregistré,
 * dans une même transaction.
 */
class StockService
{
    /**
     * Enregistre une entrée de stock et augmente la quantité disponible.
     */
    public function addStock(
        Product $product,
        int $quantity,
        User $author,
        ?string $reason = null,
        ?string $observation = null,
    ): StockEntry {
        $this->ensurePositive($quantity);

        return DB::transaction(function () use ($product, $quantity, $author, $reason, $observation) {
            $locked = $this->lock($product);

            $entry = $locked->stockEntries()->create([
                'quantity' => $quantity,
                'reason' => $reason,
                'observation' => $observation,
                'user_id' => $author->id,
            ]);

            $locked->stock_quantity += $quantity;
            $locked->save();

            $product->setRawAttributes($locked->getAttributes(), true);

            return $entry;
        });
    }

    /**
     * Enregistre une sortie de stock vers un technicien.
     *
     * @throws InsufficientStockException si la quantité dépasse le stock disponible
     */
    public function removeStock(
        Product $product,
        User $technician,
        int $quantity,
        User $author,
        string $reason,
        ?string $intervention = null,
        ?string $observation = null,
    ): StockExit {
        $this->ensurePositive($quantity);

        if (! $technician->isTechnicien()) {
            throw new InvalidArgumentException('Le bénéficiaire doit être un technicien.');
        }

        return DB::transaction(function () use ($product, $technician, $quantity, $author, $reason, $intervention, $observation) {
            // Verrou pessimiste : deux sorties simultanées ne peuvent pas lire le même stock.
            $locked = $this->lock($product);

            if ($quantity > $locked->stock_quantity) {
                throw new InsufficientStockException($locked, $quantity);
            }

            $exit = $locked->stockExits()->create([
                'technician_id' => $technician->id,
                'quantity' => $quantity,
                'reason' => $reason,
                'intervention' => $intervention,
                'observation' => $observation,
                'created_by' => $author->id,
            ]);

            $locked->stock_quantity -= $quantity;
            $locked->save();

            $product->setRawAttributes($locked->getAttributes(), true);

            return $exit;
        });
    }

    /**
     * Enregistre en une fois plusieurs produits remis à un technicien pour une même intervention.
     * Tout ou rien : si un seul produit manque, aucune sortie n'est enregistrée.
     *
     * @param  array<int, array{product: Product, quantity: int}>  $lines
     * @return array<int, StockExit> sorties, dans l'ordre des lignes
     *
     * @throws InsufficientStockException
     */
    public function removeStockLines(
        array $lines,
        User $technician,
        User $author,
        string $reason,
        ?string $intervention = null,
        ?string $observation = null,
    ): array {
        return DB::transaction(function () use ($lines, $technician, $author, $reason, $intervention, $observation) {
            // Verrous toujours pris dans le même ordre pour éviter les interblocages.
            $order = array_keys($lines);
            usort($order, fn ($a, $b) => $lines[$a]['product']->getKey() <=> $lines[$b]['product']->getKey());

            $exits = [];
            foreach ($order as $index) {
                $exits[$index] = $this->removeStock(
                    $lines[$index]['product'],
                    $technician,
                    $lines[$index]['quantity'],
                    $author,
                    $reason,
                    $intervention,
                    $observation,
                );
            }
            ksort($exits);

            return $exits;
        });
    }

    private function lock(Product $product): Product
    {
        return Product::query()->lockForUpdate()->findOrFail($product->getKey());
    }

    private function ensurePositive(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('La quantité doit être strictement supérieure à zéro.');
        }
    }
}
