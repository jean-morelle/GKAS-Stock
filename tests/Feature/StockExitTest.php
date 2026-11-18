<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StockExitTest extends TestCase
{
    use RefreshDatabase;

    private User $responsable;

    private User $technician;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responsable = User::factory()->responsable()->create();
        $this->technician = User::factory()->technicien()->create();
    }

    private function exitPayload(Product $product, mixed $quantity): array
    {
        return [
            'technician_id' => $this->technician->id,
            'lines' => [['product_id' => $product->id, 'quantity' => $quantity]],
            'reason' => 'Installation réseau',
            'intervention' => 'Client ABC',
        ];
    }

    public function test_an_exit_decreases_the_stock(): void
    {
        $product = Product::factory()->withStock(100)->create();

        $this->actingAs($this->responsable)
            ->post(route('stock.exits.store'), $this->exitPayload($product, 20))
            ->assertRedirect(route('stock.exits.index'))
            ->assertSessionHas('success');

        $this->assertSame(80, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_exits', [
            'product_id' => $product->id,
            'technician_id' => $this->technician->id,
            'quantity' => 20,
            'reason' => 'Installation réseau',
            'intervention' => 'Client ABC',
            'created_by' => $this->responsable->id,
        ]);
    }

    public function test_an_exit_greater_than_the_available_stock_is_refused(): void
    {
        $product = Product::factory()->withStock(80)->create();

        $this->actingAs($this->responsable)
            ->post(route('stock.exits.store'), $this->exitPayload($product, 100))
            ->assertSessionHasErrors(['lines.0.quantity' => 'Stock insuffisant. Disponible : 80 unité, demandé : 100 unité.']);

        $this->assertSame(80, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_exits', 0);
    }

    public function test_the_whole_stock_can_be_taken_out(): void
    {
        $product = Product::factory()->withStock(15)->create();

        $this->actingAs($this->responsable)
            ->post(route('stock.exits.store'), $this->exitPayload($product, 15))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $product->fresh()->stock_quantity);
    }

    #[DataProvider('invalidQuantities')]
    public function test_an_exit_with_a_non_positive_quantity_is_refused(int $quantity): void
    {
        $product = Product::factory()->withStock(50)->create();

        $this->actingAs($this->responsable)
            ->post(route('stock.exits.store'), $this->exitPayload($product, $quantity))
            ->assertSessionHasErrors('lines.0.quantity');

        $this->assertSame(50, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_exits', 0);
    }

    public static function invalidQuantities(): array
    {
        return ['zéro' => [0], 'moins cinq' => [-5], 'moins dix' => [-10]];
    }

    public function test_an_exit_must_target_a_technician(): void
    {
        $product = Product::factory()->withStock(50)->create();
        $payload = ['technician_id' => $this->responsable->id] + $this->exitPayload($product, 5);

        $this->actingAs($this->responsable)
            ->post(route('stock.exits.store'), $payload)
            ->assertSessionHasErrors('technician_id');

        $this->assertSame(50, $product->fresh()->stock_quantity);
    }

    public function test_the_service_refuses_insufficient_stock_and_rolls_back(): void
    {
        $product = Product::factory()->withStock(5)->create();

        try {
            app(StockService::class)->removeStock($product, $this->technician, 6, $this->responsable, 'Test');
            $this->fail('Une InsufficientStockException était attendue.');
        } catch (InsufficientStockException) {
            // attendu
        }

        $this->assertSame(5, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_exits', 0);
    }

    public function test_the_service_refuses_non_positive_quantities(): void
    {
        $product = Product::factory()->withStock(5)->create();

        $this->expectException(InvalidArgumentException::class);

        app(StockService::class)->addStock($product, 0, $this->responsable);
    }

    public function test_several_products_can_be_taken_in_one_exit(): void
    {
        $camera = Product::factory()->withStock(10)->create();
        $cable = Product::factory()->withStock(300)->create();

        $this->actingAs($this->technician)
            ->post(route('stock.exits.store'), [
                'lines' => [
                    ['product_id' => $camera->id, 'quantity' => 4],
                    ['product_id' => $cable->id, 'quantity' => 120],
                ],
                'reason' => 'Installation caméras',
                'intervention' => 'Client ABC',
            ])
            ->assertRedirect(route('my-exits.index'))
            ->assertSessionHas('success');

        $this->assertSame(6, $camera->fresh()->stock_quantity);
        $this->assertSame(180, $cable->fresh()->stock_quantity);
        $this->assertSame(2, $this->technician->stockExits()->where('intervention', 'Client ABC')->count());
    }

    public function test_a_multi_product_exit_is_all_or_nothing(): void
    {
        $camera = Product::factory()->withStock(10)->create();
        $cable = Product::factory()->withStock(50)->create();

        $this->actingAs($this->technician)
            ->post(route('stock.exits.store'), [
                'lines' => [
                    ['product_id' => $camera->id, 'quantity' => 4],
                    ['product_id' => $cable->id, 'quantity' => 120],
                ],
                'reason' => 'Installation caméras',
            ])
            ->assertSessionHasErrors('lines.1.quantity');

        $this->assertSame(10, $camera->fresh()->stock_quantity);
        $this->assertSame(50, $cable->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_exits', 0);
    }

    public function test_the_same_product_cannot_appear_twice(): void
    {
        $product = Product::factory()->withStock(50)->create();

        $this->actingAs($this->technician)
            ->post(route('stock.exits.store'), [
                'lines' => [
                    ['product_id' => $product->id, 'quantity' => 1],
                    ['product_id' => $product->id, 'quantity' => 2],
                ],
                'reason' => 'Installation',
            ])
            ->assertSessionHasErrors('lines.1.product_id');

        $this->assertDatabaseCount('stock_exits', 0);
    }

    public function test_an_exit_needs_at_least_one_product(): void
    {
        $this->actingAs($this->technician)
            ->post(route('stock.exits.store'), ['reason' => 'Installation'])
            ->assertSessionHasErrors('lines');
    }
}
