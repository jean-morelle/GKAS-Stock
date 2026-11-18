<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_technician_records_their_own_exit(): void
    {
        $technician = User::factory()->technicien()->create();
        $product = Product::factory()->withStock(50)->create();

        $this->actingAs($technician)->get(route('stock.exits.create'))
            ->assertOk()
            ->assertDontSee('Sélectionner un technicien');

        $this->post(route('stock.exits.store'), [
            'lines' => [['product_id' => $product->id, 'quantity' => 10]],
            'reason' => 'Installation caméras',
            'intervention' => 'Client ABC',
        ])->assertRedirect(route('my-exits.index'));

        $this->assertSame(40, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_exits', [
            'product_id' => $product->id,
            'technician_id' => $technician->id,
            'created_by' => $technician->id,
            'quantity' => 10,
        ]);
    }

    public function test_a_technician_cannot_record_an_exit_for_a_colleague(): void
    {
        $technician = User::factory()->technicien()->create();
        $colleague = User::factory()->technicien()->create();
        $product = Product::factory()->withStock(50)->create();

        $this->actingAs($technician)->post(route('stock.exits.store'), [
            'technician_id' => $colleague->id,
            'lines' => [['product_id' => $product->id, 'quantity' => 10]],
            'reason' => 'Tentative',
        ]);

        $this->assertDatabaseHas('stock_exits', ['technician_id' => $technician->id]);
        $this->assertDatabaseMissing('stock_exits', ['technician_id' => $colleague->id]);
    }

    public function test_a_technician_cannot_take_more_than_the_stock(): void
    {
        $technician = User::factory()->technicien()->create();
        $product = Product::factory()->withStock(5)->create();

        $this->actingAs($technician)
            ->post(route('stock.exits.store'), [
                'lines' => [['product_id' => $product->id, 'quantity' => 6]],
                'reason' => 'Installation',
            ])
            ->assertSessionHasErrors('lines.0.quantity');

        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_exits', 0);
    }

    public function test_a_technician_cannot_create_an_entry(): void
    {
        $technician = User::factory()->technicien()->create();
        $product = Product::factory()->withStock(50)->create();

        $this->actingAs($technician)
            ->post(route('stock.entries.store'), ['product_id' => $product->id, 'quantity' => 10])
            ->assertForbidden();

        $this->assertSame(50, $product->fresh()->stock_quantity);
    }

    public function test_a_technician_cannot_access_management_pages(): void
    {
        $technician = User::factory()->technicien()->create();
        $product = Product::factory()->create();

        $this->actingAs($technician);

        foreach ([
            route('stock.exits.index'),
            route('stock.entries.index'),
            route('stock.movements.index'),
            route('stock.alerts.index'),
            route('categories.index'),
            route('technicians.index'),
            route('products.create'),
            route('products.show', $product),
            route('products.edit', $product),
        ] as $url) {
            $this->get($url)->assertForbidden();
        }

        $this->put(route('products.update', $product), ['name' => 'Piraté'])->assertForbidden();
        $this->delete(route('products.destroy', $product))->assertForbidden();
    }

    public function test_a_technician_only_sees_their_own_exits(): void
    {
        $responsable = User::factory()->responsable()->create();
        $kossi = User::factory()->technicien()->create(['name' => 'Kossi']);
        $ama = User::factory()->technicien()->create(['name' => 'Ama']);
        $product = Product::factory()->withStock(100)->create();

        $stock = app(StockService::class);
        $stock->removeStock($product, $kossi, 10, $responsable, 'Intervention Kossi', 'Client KOSSI-01');
        $stock->removeStock($product, $ama, 20, $responsable, 'Intervention Ama', 'Client AMA-02');

        $this->actingAs($kossi)
            ->get(route('my-exits.index'))
            ->assertOk()
            ->assertSee('Client KOSSI-01')
            ->assertDontSee('Client AMA-02');

        // Pas d'accès à l'historique d'un autre technicien.
        $this->get(route('technicians.show', $ama))->assertForbidden();
    }

    public function test_a_technician_can_browse_available_products_read_only(): void
    {
        $technician = User::factory()->technicien()->create();
        $product = Product::factory()->withStock(12)->create(['name' => 'Switch 8 ports']);

        $this->actingAs($technician)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('Switch 8 ports')
            ->assertDontSee(route('products.edit', $product));
    }

    public function test_a_responsable_cannot_access_the_technician_space(): void
    {
        $this->actingAs(User::factory()->responsable()->create())
            ->get(route('my-exits.index'))
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->post(route('stock.exits.store'))->assertRedirect(route('login'));
    }

    public function test_the_role_cannot_be_mass_assigned_through_the_profile(): void
    {
        $technician = User::factory()->technicien()->create();

        $this->actingAs($technician)->patch(route('profile.update'), [
            'name' => $technician->name,
            'email' => $technician->email,
            'role' => 'responsable',
        ]);

        $this->assertTrue($technician->fresh()->isTechnicien());
    }

    public function test_a_product_with_movements_cannot_be_deleted(): void
    {
        $responsable = User::factory()->responsable()->create();
        $product = Product::factory()->create();
        app(StockService::class)->addStock($product, 5, $responsable);

        $this->actingAs($responsable)
            ->delete(route('products.destroy', $product))
            ->assertSessionHas('error');

        $this->assertModelExists($product);
    }

    public function test_a_product_without_movements_can_be_deleted(): void
    {
        $responsable = User::factory()->responsable()->create();
        $product = Product::factory()->create();

        $this->actingAs($responsable)
            ->delete(route('products.destroy', $product))
            ->assertRedirect(route('products.index'));

        $this->assertModelMissing($product);
    }

    public function test_a_category_with_products_cannot_be_deleted(): void
    {
        $responsable = User::factory()->responsable()->create();
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $this->actingAs($responsable)
            ->delete(route('categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_the_stock_cannot_be_set_through_the_product_form(): void
    {
        $responsable = User::factory()->responsable()->create();
        $product = Product::factory()->withStock(10)->create();

        $this->actingAs($responsable)->put(route('products.update', $product), [
            'category_id' => $product->category_id,
            'name' => $product->name,
            'unit' => 'unité',
            'alert_threshold' => 5,
            'stock_quantity' => 9999,
        ])->assertSessionHasNoErrors();

        $this->assertSame(10, $product->fresh()->stock_quantity);
    }
}
