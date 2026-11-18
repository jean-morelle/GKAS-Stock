<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\MovementHistory;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $responsable;

    private User $kossi;

    private User $ama;

    private Product $cable;

    private Product $rj45;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responsable = User::factory()->responsable()->create(['name' => 'Responsable GKAS']);
        $this->kossi = User::factory()->technicien()->create(['name' => 'Kossi']);
        $this->ama = User::factory()->technicien()->create(['name' => 'Ama']);
        $this->cable = Product::factory()->create(['name' => 'Câble CAT6']);
        $this->rj45 = Product::factory()->create(['name' => 'Connecteur RJ45']);

        $stock = app(StockService::class);
        $stock->addStock($this->cable, 305, $this->responsable, 'Livraison');
        $stock->addStock($this->rj45, 100, $this->responsable, 'Livraison');
        $stock->removeStock($this->cable, $this->kossi, 50, $this->responsable, 'Installation réseau', 'Client ABC');
        $stock->removeStock($this->cable, $this->ama, 30, $this->responsable, 'Extension réseau', 'Client XYZ');
        $stock->removeStock($this->rj45, $this->kossi, 20, $this->responsable, 'Installation réseau', 'Client XYZ');
    }

    public function test_all_movements_are_recorded_with_their_actors(): void
    {
        $this->assertDatabaseCount('stock_entries', 2);
        $this->assertDatabaseCount('stock_exits', 3);

        $movements = app(MovementHistory::class)->query()->get();

        $this->assertCount(5, $movements);
        $this->assertSame(2, $movements->where('type', 'entry')->count());
        $this->assertSame(3, $movements->where('type', 'exit')->count());

        $exit = $movements->firstWhere('intervention', 'Client ABC');
        $this->assertSame('Kossi', $exit->technician_name);
        $this->assertSame('Responsable GKAS', $exit->user_name);
        $this->assertSame('Câble CAT6', $exit->product_name);
        $this->assertSame(50, (int) $exit->quantity);
    }

    public function test_stock_always_matches_the_sum_of_movements(): void
    {
        foreach ([$this->cable, $this->rj45] as $product) {
            $product->refresh();
            $expected = $product->stockEntries()->sum('quantity') - $product->stockExits()->sum('quantity');
            $this->assertSame((int) $expected, $product->stock_quantity);
        }

        $this->assertSame(225, $this->cable->stock_quantity);
        $this->assertSame(80, $this->rj45->stock_quantity);
    }

    public function test_movements_can_be_filtered(): void
    {
        $history = app(MovementHistory::class);

        $this->assertCount(2, $history->query(['type' => 'entry'])->get());
        $this->assertCount(3, $history->query(['type' => 'exit'])->get());
        $this->assertCount(3, $history->query(['product_id' => $this->cable->id])->get());
        $this->assertCount(2, $history->query(['technician_id' => $this->kossi->id])->get());
        $this->assertCount(0, $history->query(['date_from' => now()->addDay()->toDateString()])->get());
        $this->assertCount(5, $history->query(['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()])->get());
    }

    public function test_the_movements_page_lists_entries_and_exits(): void
    {
        $this->actingAs($this->responsable)
            ->get(route('stock.movements.index', ['technician_id' => $this->kossi->id]))
            ->assertOk()
            ->assertSee('Client ABC')
            ->assertDontSee('Extension réseau');
    }

    public function test_the_product_page_shows_its_history(): void
    {
        $this->actingAs($this->responsable)
            ->get(route('products.show', $this->cable))
            ->assertOk()
            ->assertSee('225 unité')
            ->assertSee('Kossi')
            ->assertSee('Ama')
            ->assertSee('+305 unité');
    }

    public function test_the_technician_page_shows_their_exits_for_a_period(): void
    {
        $this->actingAs($this->responsable)
            ->get(route('technicians.show', [$this->kossi, 'date_from' => today()->toDateString()]))
            ->assertOk()
            ->assertSee('Câble CAT6')
            ->assertSee('Connecteur RJ45')
            ->assertSee('Client ABC')
            ->assertDontSee('Extension réseau');
    }
}
