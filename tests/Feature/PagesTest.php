<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Vérifie que chaque écran s'affiche avec les données de démonstration.
 */
class PagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_every_responsable_page_renders(): void
    {
        $responsable = User::where('email', 'admin@gkasgroup.com')->firstOrFail();
        $product = Product::where('reference', 'CAT6-305')->firstOrFail();
        $technician = User::technicians()->firstOrFail();
        $category = Category::firstOrFail();

        $this->actingAs($responsable);

        foreach ([
            route('dashboard'),
            route('products.index'),
            route('products.index', ['search' => 'cat6', 'status' => 'faible', 'category' => $category->id]),
            route('products.create'),
            route('products.show', $product),
            route('products.edit', $product),
            route('categories.index'),
            route('categories.create'),
            route('categories.edit', $category),
            route('stock.entries.index'),
            route('stock.entries.create', ['product' => $product->id]),
            route('stock.exits.index'),
            route('stock.exits.index', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]),
            route('stock.entries.index', ['date_from' => today()->toDateString()]),
            route('dashboard', ['period' => '30d']),
            route('stock.exits.create'),
            route('stock.movements.index'),
            route('stock.movements.index', ['type' => 'exit', 'date_from' => today()->toDateString()]),
            route('stock.alerts.index'),
            route('technicians.index'),
            route('technicians.create'),
            route('technicians.show', $technician),
            route('technicians.edit', $technician),
            route('profile.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_every_technician_page_renders(): void
    {
        $this->actingAs(User::where('email', 'kossi.mensah@gkasgroup.com')->firstOrFail());

        $this->get(route('dashboard'))->assertOk()->assertSee('Mes dernières interventions');
        $this->get(route('products.index'))->assertOk()->assertSee('Produits disponibles');
        $this->get(route('my-exits.index'))->assertOk()->assertSee('Client ABC');
        $this->get(route('stock.exits.create'))->assertOk()->assertSee('Matériel à sortir');
        $this->get(route('stock.exits.create', ['product' => Product::where('reference', 'CAT6-305')->value('id')]))->assertOk();
        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_technician_dashboard_groups_exits_by_intervention(): void
    {
        $this->actingAs(User::where('email', 'kossi.mensah@gkasgroup.com')->firstOrFail())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('interventions', fn ($interventions) => $interventions->count() === 1
                && $interventions->first()->intervention === 'Client ABC'
                && $interventions->first()->exits->count() === 2);
    }

    public function test_my_exits_can_be_filtered(): void
    {
        $this->actingAs(User::where('email', 'ama.agbeko@gkasgroup.com')->firstOrFail());

        $this->get(route('my-exits.index', ['search' => 'xyz']))
            ->assertOk()
            ->assertViewHas('exits', fn ($exits) => $exits->total() === 1);

        $this->get(route('my-exits.index', ['search' => 'ABC']))
            ->assertViewHas('exits', fn ($exits) => $exits->total() === 0);

        $this->get(route('my-exits.index', ['date_to' => today()->subDay()->toDateString()]))
            ->assertViewHas('exits', fn ($exits) => $exits->total() === 0);
    }

    public function test_dashboard_counters(): void
    {
        $this->actingAs(User::where('email', 'admin@gkasgroup.com')->firstOrFail())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('technicians', fn ($technicians) => $technicians->count() === 4
                && $technicians->sum('exits_count') === 4
                && $technicians->first()->name === 'Kossi Mensah')
            ->assertViewHas('stats', fn (array $stats) => $stats['entries'] === 9
                && $stats['exits'] === 4
                && $stats['low'] === 1
                && $stats['out'] === 1);
    }

    public function test_lists_are_paginated_by_five(): void
    {
        config(['stock.per_page' => 5]);

        $this->actingAs(User::where('email', 'admin@gkasgroup.com')->firstOrFail());

        // 10 produits de démo : 5 en page 1, 5 en page 2.
        $this->get(route('products.index'))
            ->assertOk()
            ->assertViewHas('products', fn ($p) => $p->count() === 5 && $p->total() === 10 && $p->lastPage() === 2)
            ->assertSee('page=2');

        $this->get(route('products.index', ['page' => 2]))
            ->assertViewHas('products', fn ($p) => $p->count() === 5 && $p->currentPage() === 2);

        // 13 mouvements de démo : 3 pages (5 + 5 + 3).
        $this->get(route('stock.movements.index', ['page' => 3]))
            ->assertViewHas('movements', fn ($m) => $m->count() === 3 && $m->lastPage() === 3);
    }

    public function test_the_technician_id_route_only_resolves_technicians(): void
    {
        $responsable = User::where('email', 'admin@gkasgroup.com')->firstOrFail();

        $this->actingAs($responsable)
            ->get(route('technicians.show', $responsable->id))
            ->assertNotFound();
    }
}
