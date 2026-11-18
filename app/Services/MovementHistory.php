<?php

namespace App\Services;

use App\Enums\ProductUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Vue unifiée (lecture seule) des entrées et sorties de stock.
 *
 * Filtres acceptés : type (entry|exit), product_id, technician_id, date_from, date_to.
 */
class MovementHistory
{
    public function paginate(array $filters = [], ?int $perPage = null): LengthAwarePaginator
    {
        return $this->query($filters)
            ->paginate($perPage ?? config('stock.per_page'))
            ->withQueryString()
            ->through(fn (object $row) => $this->hydrate($row));
    }

    public function query(array $filters = []): Builder
    {
        $type = $filters['type'] ?? null;

        // Un filtre par technicien ne concerne que les sorties.
        $withEntries = $type !== 'exit' && empty($filters['technician_id']);
        $withExits = $type !== 'entry';

        $parts = array_filter([
            $withEntries ? $this->entries($filters) : null,
            $withExits ? $this->exits($filters) : null,
        ]);

        $union = array_shift($parts);
        foreach ($parts as $part) {
            $union->unionAll($part);
        }

        return DB::query()
            ->fromSub($union, 'movements')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    private function hydrate(object $row): object
    {
        $row->created_at = Carbon::parse($row->created_at);
        $row->unit = ProductUnit::from($row->unit);

        return $row;
    }

    private function entries(array $filters): Builder
    {
        $query = DB::table('stock_entries as e')
            ->join('products as p', 'p.id', '=', 'e.product_id')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->selectRaw("'entry' as type, e.id, e.product_id, p.name as product_name, p.unit, e.quantity,
                NULL as technician_id, NULL as technician_name, u.name as user_name,
                e.reason, NULL as intervention, e.observation, e.created_at");

        return $this->applyCommonFilters($query, 'e', $filters);
    }

    private function exits(array $filters): Builder
    {
        $query = DB::table('stock_exits as x')
            ->join('products as p', 'p.id', '=', 'x.product_id')
            ->join('users as t', 't.id', '=', 'x.technician_id')
            ->join('users as u', 'u.id', '=', 'x.created_by')
            ->selectRaw("'exit' as type, x.id, x.product_id, p.name as product_name, p.unit, x.quantity,
                x.technician_id, t.name as technician_name, u.name as user_name,
                x.reason, x.intervention, x.observation, x.created_at")
            ->when($filters['technician_id'] ?? null, fn (Builder $q, $id) => $q->where('x.technician_id', $id));

        return $this->applyCommonFilters($query, 'x', $filters);
    }

    private function applyCommonFilters(Builder $query, string $alias, array $filters): Builder
    {
        return $query
            ->when($filters['product_id'] ?? null, fn (Builder $q, $id) => $q->where("{$alias}.product_id", $id))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->where("{$alias}.created_at", '>=', Carbon::parse($date)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->where("{$alias}.created_at", '<=', Carbon::parse($date)->endOfDay()));
    }
}
