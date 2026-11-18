<?php

namespace App\Http\Controllers;

use App\Enums\ProductUnit;
use App\Enums\StockStatus;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\MovementHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Product::class);

        $filters = [
            'search' => $request->string('search')->trim()->toString(),
            'category' => $request->integer('category') ?: null,
            'status' => StockStatus::tryFrom($request->string('status')->toString()),
        ];

        $products = Product::query()
            ->with('category')
            ->withExists(['stockEntries', 'stockExits'])
            ->search($filters['search'])
            ->when($filters['category'], fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['status'], fn ($q, StockStatus $status) => match ($status) {
                StockStatus::OutOfStock => $q->outOfStock(),
                StockStatus::Low => $q->lowStock(),
                StockStatus::Available => $q->whereColumn('stock_quantity', '>', 'alert_threshold'),
            })
            ->orderBy('name')
            ->paginate(config('stock.per_page'))
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('products.form', $this->formData(new Product));
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        return to_route('products.show', $product)
            ->with('success', "Produit « {$product->name} » créé. Enregistrez une entrée pour alimenter son stock.");
    }

    public function show(Product $product, MovementHistory $history): View
    {
        Gate::authorize('view', $product);

        $product->load('category');

        return view('products.show', [
            'product' => $product,
            'totalIn' => (int) $product->stockEntries()->sum('quantity'),
            'totalOut' => (int) $product->stockExits()->sum('quantity'),
            'movements' => $history->paginate(['product_id' => $product->id]),
        ]);
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        return view('products.form', $this->formData($product));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return to_route('products.show', $product)->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        if ($product->hasMovements()) {
            return back()->with('error', "Le produit « {$product->name} » possède un historique de mouvements : il ne peut pas être supprimé.");
        }

        $product->delete();

        return to_route('products.index')->with('success', "Produit « {$product->name} » supprimé.");
    }

    private function formData(Product $product): array
    {
        return [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'units' => ProductUnit::cases(),
        ];
    }
}
