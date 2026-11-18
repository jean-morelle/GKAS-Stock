<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockEntry;
use App\Models\StockExit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return $user->isResponsable()
            ? $this->responsable($request, $user)
            : $this->technicien($user);
    }

    private function responsable(Request $request, User $user): View
    {
        $periods = $this->periods($user);
        $period = array_key_exists($request->query('period'), $periods)
            ? $request->query('period')
            : array_key_first($periods);
        $since = $this->periodStart($period, $user);

        return view('dashboard.responsable', [
            'periods' => $periods,
            'period' => $period,
            'since' => $since,
            'stats' => [
                'entries' => StockEntry::where('created_at', '>=', $since)->count(),
                'exits' => StockExit::where('created_at', '>=', $since)->count(),
                'low' => Product::lowStock()->count(),
                'out' => Product::outOfStock()->count(),
            ],
            'technicians' => $this->technicianSummary($since),
        ]);
    }

    /**
     * Périodes proposées ; la première est celle par défaut.
     * « Depuis ma dernière visite » n'existe qu'à partir de la deuxième visite.
     *
     * @return array<string, string>
     */
    private function periods(User $user): array
    {
        return array_filter([
            'visit' => $user->previous_visit_at
                ? 'Depuis ma dernière visite ('.$user->previous_visit_at->format('d/m à H:i').')'
                : null,
            'today' => "Aujourd'hui",
            '7d' => '7 derniers jours',
            '30d' => '30 derniers jours',
        ]);
    }

    private function periodStart(string $period, User $user): Carbon
    {
        return match ($period) {
            'visit' => $user->previous_visit_at,
            '7d' => today()->subDays(6),
            '30d' => today()->subDays(29),
            default => today(),
        };
    }

    /**
     * Pour chaque technicien : nombre de sorties et matériel reçu (cumul par produit) depuis $since.
     * Les techniciens sans sortie sont listés aussi, en fin de tableau.
     */
    private function technicianSummary(Carbon $since): Collection
    {
        $lines = StockExit::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('technician_id, product_id, SUM(quantity) as total, COUNT(*) as exits_count, MAX(created_at) as last_exit_at')
            ->groupBy('technician_id', 'product_id')
            ->with('product:id,name,unit')
            ->get()
            ->groupBy('technician_id');

        return User::technicians()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function (User $technician) use ($lines) {
                $products = ($lines[$technician->id] ?? collect())->sortByDesc('total')->values();

                return (object) [
                    'id' => $technician->id,
                    'name' => $technician->name,
                    'exits_count' => (int) $products->sum('exits_count'),
                    'products' => $products,
                    'last_exit_at' => $products->isEmpty() ? null : Carbon::parse($products->max('last_exit_at')),
                ];
            })
            ->sortByDesc('exits_count')
            ->values();
    }

    private function technicien(User $user): View
    {
        $exits = $user->stockExits();
        $month = (clone $exits)->where('created_at', '>=', now()->startOfMonth());

        // Les produits d'une même sortie partagent date, motif et intervention : on les regroupe.
        $interventions = (clone $exits)
            ->with('product')
            ->latest()
            ->latest('id')
            ->limit(40)
            ->get()
            ->groupBy(fn (StockExit $exit) => $exit->created_at->format('Y-m-d H:i').'|'.$exit->reason.'|'.$exit->intervention)
            ->take(6)
            ->map(fn (Collection $group) => (object) [
                'date' => $group->first()->created_at,
                'reason' => $group->first()->reason,
                'intervention' => $group->first()->intervention,
                'exits' => $group,
            ])
            ->values();

        return view('dashboard.technicien', [
            'stats' => [
                'month' => (clone $month)->count(),
                'products' => (clone $month)->distinct()->count('product_id'),
                'total' => (clone $exits)->count(),
            ],
            'interventions' => $interventions,
        ]);
    }
}
