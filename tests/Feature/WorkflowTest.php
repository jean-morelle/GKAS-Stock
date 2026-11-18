<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Scénario de recette de bout en bout (cahier des charges, section 31).
 */
class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_stock_workflow(): void
    {
        $responsable = User::factory()->responsable()->create(['email' => 'admin@gkasgroup.com']);
        $technician = User::factory()->technicien()->create(['name' => 'Kossi Mensah']);

        // 1. Connexion responsable
        $this->post(route('login'), ['email' => 'admin@gkasgroup.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($responsable);
        $this->get(route('dashboard'))->assertOk()->assertSee('Matériel pris');

        // 2. Création d'une catégorie
        $this->post(route('categories.store'), ['name' => 'Réseau'])->assertRedirect(route('categories.index'));
        $category = Category::where('name', 'Réseau')->firstOrFail();

        // 3. Création d'un produit
        $this->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Câble réseau CAT6',
            'reference' => 'CAT6-305',
            'unit' => 'mètre',
            'alert_threshold' => 10,
        ])->assertSessionHasNoErrors();
        $product = Product::where('reference', 'CAT6-305')->firstOrFail();

        // 4. Stock initial = 0
        $this->assertSame(0, $product->stock_quantity);

        // 5-6. Entrée de 100 → stock = 100
        $this->post(route('stock.entries.store'), ['product_id' => $product->id, 'quantity' => 100, 'reason' => 'Achat'])
            ->assertSessionHasNoErrors();
        $this->assertSame(100, $product->fresh()->stock_quantity);

        // 7-9. Sortie de 20 pour le technicien → stock = 80
        $this->get(route('stock.exits.create'))->assertOk()->assertSee('Kossi Mensah');
        $this->post(route('stock.exits.store'), [
            'technician_id' => $technician->id,
            'lines' => [['product_id' => $product->id, 'quantity' => 20]],
            'reason' => 'Installation réseau',
            'intervention' => 'Client ABC',
        ])->assertSessionHasNoErrors();
        $this->assertSame(80, $product->fresh()->stock_quantity);

        // 10. Historique
        $this->get(route('stock.movements.index'))
            ->assertOk()
            ->assertSee('+100 m')
            ->assertSee('−20 m')
            ->assertSee('Kossi Mensah');

        // 11. Le technicien possède une sortie
        $this->assertSame(1, $technician->stockExits()->count());
        $this->get(route('technicians.show', $technician))->assertOk()->assertSee('Client ABC');

        // 12-13. Sortie de 100 → refusée
        $this->from(route('stock.exits.create'))->post(route('stock.exits.store'), [
            'technician_id' => $technician->id,
            'lines' => [['product_id' => $product->id, 'quantity' => 100]],
            'reason' => 'Installation réseau',
        ])
            ->assertRedirect(route('stock.exits.create'))
            ->assertSessionHasErrors('lines.0.quantity');

        // 14. Le stock est toujours de 80
        $this->assertSame(80, $product->fresh()->stock_quantity);
        $this->assertSame(1, $technician->stockExits()->count());

        // Le technicien voit sa sortie dans son espace
        $this->post(route('logout'));
        $this->post(route('login'), ['email' => $technician->email, 'password' => 'password']);
        $this->get(route('my-exits.index'))->assertOk()->assertSee('Câble réseau CAT6')->assertSee('Client ABC');
    }
}
