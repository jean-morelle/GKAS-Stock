<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Espace technicien : uniquement ses propres sorties (règle 9),
 * filtrables par période, produit et intervention.
 */
class MyExitController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'product' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $filters = [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'product' => isset($validated['product']) ? (int) $validated['product'] : null,
            'search' => trim($validated['search'] ?? '') ?: null,
        ];

        $base = $user->stockExits()
            ->betweenDates($filters['date_from'], $filters['date_to'])
            ->when($filters['product'], fn ($q, $id) => $q->where('product_id', $id))
            ->when($filters['search'], fn ($q, $term) => $q->where(fn ($q) => $q
                ->where('intervention', 'like', "%{$term}%")
                ->orWhere('reason', 'like', "%{$term}%")));

        $exits = (clone $base)
            ->with('product')
            ->latest()
            ->latest('id')
            ->paginate(config('stock.per_page'))
            ->withQueryString();

        // Matériel pris, cumulé par produit (selon les filtres).
        $assigned = (clone $base)
            ->selectRaw('product_id, SUM(quantity) as total, MAX(created_at) as last_at')
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->sortByDesc('last_at');

        // Seuls les produits déjà pris par ce technicien sont proposés dans le filtre.
        $products = Product::whereIn('id', $user->stockExits()->select('product_id'))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('my-exits.index', compact('exits', 'assigned', 'filters', 'products'));
    }
}
