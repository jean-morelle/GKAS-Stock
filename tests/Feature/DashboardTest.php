<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Tableau de bord du responsable : « Depuis votre dernière visite », périodes,
 * résumé par technicien et filtres par dates des listes.
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $responsable;

    private User $kossi;

    private User $ama;

    private Product $cable;

    private Product $rj45;

    private StockService $stock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-01 09:00'));

        $this->responsable = User::factory()->responsable()->create();
        $this->kossi = User::factory()->technicien()->create(['name' => 'Kossi']);
        $this->ama = User::factory()->technicien()->create(['name' => 'Ama']);
        $this->cable = Product::factory()->create(['name' => 'Câble CAT6']);
        $this->rj45 = Product::factory()->create(['name' => 'Connecteur RJ45']);

        $this->stock = app(StockService::class);
        $this->stock->addStock($this->cable, 300, $this->responsable, 'Livraison');
        $this->stock->addStock($this->rj45, 100, $this->responsable, 'Livraison');
    }

    public function test_first_visit_has_no_since_last_visit_period(): void
    {
        $this->actingAs($this->responsable)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('period', 'today')
            ->assertViewHas('periods', fn (array $periods) => ! isset($periods['visit']))
            ->assertDontSee('Depuis ma dernière visite');

        $this->responsable->refresh();
        $this->assertNotNull($this->responsable->last_seen_at);
        $this->assertNull($this->responsable->previous_visit_at);
    }

    public function test_returning_after_a_while_shows_activity_since_last_visit(): void
    {
        $this->stock->removeStock($this->cable, $this->kossi, 10, $this->responsable, 'Avant la visite');
        $this->travelTo(Carbon::parse('2026-10-01 09:10'));
        $this->actingAs($this->responsable)->get(route('dashboard'));

        // Le chef revient 10 jours plus tard ; entre-temps, deux sorties.
        $this->travelTo(Carbon::parse('2026-10-05 10:00'));
        $this->stock->removeStock($this->cable, $this->kossi, 50, $this->responsable, 'Installation');
        $this->stock->removeStock($this->rj45, $this->ama, 20, $this->responsable, 'Installation');
        $this->travelTo(Carbon::parse('2026-10-11 08:00'));

        $this->actingAs($this->responsable->fresh())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('period', 'visit')
            ->assertViewHas('since', fn (Carbon $since) => $since->equalTo('2026-10-01 09:10'))
            ->assertViewHas('technicians', fn ($technicians) => $technicians->sum('exits_count') === 2)
            ->assertSee('Depuis ma dernière visite (01/10 à 09:10)');

        $this->assertTrue($this->responsable->fresh()->previous_visit_at->equalTo('2026-10-01 09:10'));
    }

    public function test_activity_within_the_same_visit_keeps_the_previous_visit(): void
    {
        $this->responsable->forceFill(['last_seen_at' => '2026-10-01 08:30', 'previous_visit_at' => '2026-09-20 15:00'])->save();

        $this->actingAs($this->responsable)->get(route('dashboard'));
        $this->travel(30)->minutes();
        $this->actingAs($this->responsable->fresh())->get(route('dashboard'));

        $user = $this->responsable->fresh();
        $this->assertTrue($user->previous_visit_at->equalTo('2026-09-20 15:00'));
        $this->assertTrue($user->last_seen_at->equalTo('2026-10-01 09:30'));
    }

    public function test_technician_summary_totals_material_per_product(): void
    {
        $this->stock->removeStock($this->cable, $this->kossi, 50, $this->responsable, 'Installation');
        $this->stock->removeStock($this->cable, $this->kossi, 30, $this->responsable, 'Installation');
        $this->stock->removeStock($this->rj45, $this->kossi, 20, $this->responsable, 'Installation');

        $this->actingAs($this->responsable)
            ->get(route('dashboard', ['period' => '7d']))
            ->assertOk()
            ->assertViewHas('technicians', function ($technicians) {
                [$kossi, $ama] = $technicians->all();

                return $kossi->name === 'Kossi'
                    && $kossi->exits_count === 3
                    && $kossi->products->first()->product->name === 'Câble CAT6'
                    && (int) $kossi->products->first()->total === 80
                    && $ama->exits_count === 0
                    && $ama->last_exit_at === null;
            })
            ->assertSee('Aucune sortie');
    }

    public function test_period_excludes_older_movements(): void
    {
        $this->stock->removeStock($this->cable, $this->kossi, 5, $this->responsable, 'Ancienne');
        $this->travelTo(Carbon::parse('2026-10-20 09:00'));
        $this->stock->removeStock($this->cable, $this->kossi, 7, $this->responsable, 'Récente');

        $this->actingAs($this->responsable)
            ->get(route('dashboard', ['period' => '7d']))
            ->assertViewHas('technicians', fn ($technicians) => $technicians->sum('exits_count') === 1);

        $this->actingAs($this->responsable)
            ->get(route('dashboard', ['period' => '30d']))
            ->assertViewHas('technicians', fn ($technicians) => $technicians->sum('exits_count') === 2);
    }

    public function test_entries_and_exits_lists_filter_by_date(): void
    {
        $this->stock->removeStock($this->cable, $this->kossi, 5, $this->responsable, 'Ancienne');
        $this->travelTo(Carbon::parse('2026-10-03 14:00'));
        $this->stock->removeStock($this->cable, $this->kossi, 7, $this->responsable, 'Récente');
        $this->stock->addStock($this->rj45, 10, $this->responsable, 'Réassort');

        $this->actingAs($this->responsable);

        $this->get(route('stock.exits.index', ['date_from' => '2026-10-02']))
            ->assertOk()
            ->assertViewHas('exits', fn ($exits) => $exits->total() === 1 && $exits->first()->quantity === 7);

        $this->get(route('stock.exits.index', ['date_to' => '2026-10-01']))
            ->assertViewHas('exits', fn ($exits) => $exits->total() === 1 && $exits->first()->quantity === 5);

        $this->get(route('stock.entries.index', ['date_from' => '2026-10-03', 'date_to' => '2026-10-03']))
            ->assertOk()
            ->assertViewHas('entries', fn ($entries) => $entries->total() === 1 && $entries->first()->quantity === 10);
    }

    public function test_date_range_must_be_in_order(): void
    {
        $this->actingAs($this->responsable)
            ->get(route('stock.exits.index', ['date_from' => '2026-10-05', 'date_to' => '2026-10-01']))
            ->assertSessionHasErrors('date_to');
    }
}
