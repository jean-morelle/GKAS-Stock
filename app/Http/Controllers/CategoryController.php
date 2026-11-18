<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Category::class);

        $search = $request->string('search')->trim()->toString();

        $categories = Category::query()
            ->withCount('products')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(config('stock.per_page'))
            ->withQueryString();

        return view('categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        Gate::authorize('create', Category::class);

        return view('categories.form', ['category' => new Category]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        return to_route('categories.index')->with('success', "Catégorie « {$category->name} » créée.");
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('categories.form', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return to_route('categories.index')->with('success', "Catégorie « {$category->name} » mise à jour.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        if ($category->products()->exists()) {
            return back()->with('error', "Impossible de supprimer « {$category->name} » : des produits y sont associés.");
        }

        $category->delete();

        return to_route('categories.index')->with('success', "Catégorie « {$category->name} » supprimée.");
    }
}
