<?php

namespace App\Exceptions;

use App\Models\Product;
use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(
        public readonly Product $product,
        public readonly int $requested,
    ) {
        parent::__construct(sprintf(
            'Stock insuffisant. Disponible : %s, demandé : %s.',
            $product->formatQuantity($product->stock_quantity),
            $product->formatQuantity($requested),
        ));
    }
}
