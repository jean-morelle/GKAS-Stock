<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovementFilterRequest;
use App\Models\Product;
use App\Models\User;
use App\Services\MovementHistory;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function __invoke(MovementFilterRequest $request, MovementHistory $history): View
    {
        $filters = array_filter($request->validated());

        return view('stock.movements.index', [
            'movements' => $history->paginate($filters),
            'filters' => $filters,
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'technicians' => User::technicians()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
