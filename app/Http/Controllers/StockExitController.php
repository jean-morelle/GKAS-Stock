<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StockExitRequest;
use App\Models\Product;
use App\Models\StockExit;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StockExitController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', StockExit::class);

        $dates = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $filters = [
            'technician' => $request->integer('technician') ?: null,
            'product' => $request->integer('product') ?: null,
            'date_from' => $dates['date_from'] ?? null,
            'date_to' => $dates['date_to'] ?? null,
        ];

        $exits = StockExit::query()
            ->with(['product', 'technician', 'creator'])
            ->when($filters['technician'], fn ($q, $id) => $q->where('technician_id', $id))
            ->when($filters['product'], fn ($q, $id) => $q->where('product_id', $id))
            ->betweenDates($filters['date_from'], $filters['date_to'])
            ->latest()
            ->latest('id')
            ->paginate(config('stock.per_page'))
            ->withQueryString();

        return view('stock.exits.index', [
            'exits' => $exits,
            'filters' => $filters,
            'technicians' => User::technicians()->orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', StockExit::class);

        // Le technicien sort du matériel pour lui-même : pas de choix du bénéficiaire.
        $technicians = $request->user()->isResponsable()
            ? User::technicians()->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('stock.exits.create', [
            'products' => Product::with('category:id,name')->orderBy('name')->get(),
            'technicians' => $technicians,
            'selectedProduct' => $request->integer('product') ?: null,
            'selectedTechnician' => $request->integer('technician') ?: null,
        ]);
    }

    public function store(StockExitRequest $request, StockService $stock): RedirectResponse
    {
        $data = $request->validated();
        $technician = User::technicians()->findOrFail($data['technician_id']);
        $products = Product::findMany(array_column($data['lines'], 'product_id'))->keyBy('id');

        $lines = array_map(fn (array $line) => [
            'product' => $products[$line['product_id']],
            'quantity' => (int) $line['quantity'],
        ], array_values($data['lines']));

        try {
            $stock->removeStockLines(
                $lines,
                $technician,
                $request->user(),
                $data['reason'],
                $data['intervention'] ?? null,
                $data['observation'] ?? null,
            );
        } catch (InsufficientStockException $e) {
            // L'erreur est rattachée à la ligne du produit concerné.
            $index = collect($lines)->search(fn (array $line) => $line['product']->is($e->product));

            return back()->withInput()->withErrors(["lines.{$index}.quantity" => $e->getMessage()]);
        }

        $summary = collect($lines)
            ->map(fn (array $line) => sprintf(
                '%s de « %s » (reste %s)',
                $line['product']->formatQuantity($line['quantity']),
                $line['product']->name,
                $line['product']->formatQuantity($line['product']->stock_quantity),
            ))
            ->join(', ');

        if ($request->user()->isTechnicien()) {
            return to_route('my-exits.index')->with('success', "Sortie enregistrée : {$summary}.");
        }

        return to_route('stock.exits.index')->with('success', "Sortie enregistrée pour {$technician->name} : {$summary}.");
    }
}
