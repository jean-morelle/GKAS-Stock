<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockAlertController extends Controller
{
    public function __invoke(Request $request): View
    {
        Gate::authorize('viewAlerts', Product::class);

        $products = Product::query()
            ->with('category')
            ->needsAttention()
            // Ruptures en premier, puis les plus proches de zéro.
            ->orderBy('stock_quantity')
            ->orderBy('name')
            ->get();

        return view('stock.alerts.index', [
            'outOfStock' => $products->filter(fn (Product $p) => $p->stock_quantity === 0),
            'lowStock' => $products->filter(fn (Product $p) => $p->stock_quantity > 0),
        ]);
    }
}
