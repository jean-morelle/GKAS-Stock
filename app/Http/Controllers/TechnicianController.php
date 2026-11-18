<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\TechnicianRequest;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TechnicianController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        $search = $request->string('search')->trim()->toString();

        $technicians = User::technicians()
            ->withCount('stockExits')
            ->withMax('stockExits', 'created_at')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->orderBy('name')
            ->paginate(config('stock.per_page'))
            ->withQueryString();

        return view('technicians.index', compact('technicians', 'search'));
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('technicians.form', ['technician' => new User]);
    }

    public function store(TechnicianRequest $request): RedirectResponse
    {
        $technician = new User($request->validated());
        $technician->role = UserRole::Technicien;
        $technician->email_verified_at = now();
        $technician->save();

        return to_route('technicians.index')->with('success', "Compte technicien « {$technician->name} » créé.");
    }

    /**
     * Historique des sorties d'un technicien, filtrable par période et produit.
     */
    public function show(Request $request, User $technician): View
    {
        Gate::authorize('view', $technician);

        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'product' => ['nullable', 'integer'],
        ]);

        $from = isset($validated['date_from']) ? Carbon::parse($validated['date_from'])->startOfDay() : null;
        $to = isset($validated['date_to']) ? Carbon::parse($validated['date_to'])->endOfDay() : null;
        $productId = $validated['product'] ?? null;

        $base = $technician->stockExits()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->when($productId, fn ($q) => $q->where('product_id', $productId));

        $exits = (clone $base)
            ->with('product')
            ->latest()
            ->latest('id')
            ->paginate(config('stock.per_page'))
            ->withQueryString();

        // Matériel reçu sur la période, cumulé par produit.
        $summary = (clone $base)
            ->selectRaw('product_id, SUM(quantity) as total, COUNT(*) as operations')
            ->groupBy('product_id')
            ->with('product')
            ->get()
            ->sortByDesc('total');

        return view('technicians.show', [
            'technician' => $technician,
            'exits' => $exits,
            'summary' => $summary,
            'filters' => $validated,
            'technicians' => User::technicians()->orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(User $technician): View
    {
        Gate::authorize('update', $technician);

        return view('technicians.form', compact('technician'));
    }

    public function update(TechnicianRequest $request, User $technician): RedirectResponse
    {
        $data = $request->validated();

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $technician->update($data);

        return to_route('technicians.index')->with('success', "Technicien « {$technician->name} » mis à jour.");
    }

    public function destroy(User $technician): RedirectResponse
    {
        Gate::authorize('delete', $technician);

        if ($technician->stockExits()->exists()) {
            return back()->with('error', "« {$technician->name} » possède un historique de sorties : le compte ne peut pas être supprimé.");
        }

        $technician->delete();

        return to_route('technicians.index')->with('success', "Technicien « {$technician->name} » supprimé.");
    }
}
