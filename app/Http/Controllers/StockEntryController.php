<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockEntryRequest;
use App\Models\Product;
use App\Models\StockEntry;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockEntryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', StockEntry::class);

        $dates = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $filters = [
            'product' => $request->integer('product') ?: null,
            'date_from' => $dates['date_from'] ?? null,
            'date_to' => $dates['date_to'] ?? null,
        ];

        $entries = StockEntry::query()
            ->with(['product', 'user'])
            ->when($filters['product'], fn ($q, $id) => $q->where('product_id', $id))
            ->betweenDates($filters['date_from'], $filters['date_to'])
            ->latest()
            ->latest('id')
            ->paginate(config('stock.per_page'))
            ->withQueryString();

        return view('stock.entries.index', [
            'entries' => $entries,
            'filters' => $filters,
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', StockEntry::class);

        return view('stock.entries.create', [
            'products' => Product::orderBy('name')->get(),
            'selectedProduct' => $request->integer('product') ?: null,
        ]);
    }

    public function store(StockEntryRequest $request, StockService $stock): RedirectResponse
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        $stock->addStock(
            $product,
            (int) $data['quantity'],
            $request->user(),
            $data['reason'] ?? null,
            $data['observation'] ?? null,
        );

        return to_route('stock.entries.index')->with('success', sprintf(
            'Entrée enregistrée : +%s de « %s ». Nouveau stock : %s.',
            $product->formatQuantity((int) $data['quantity']),
            $product->name,
            $product->formatQuantity($product->stock_quantity),
        ));
    }
}
