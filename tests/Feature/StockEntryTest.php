<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StockEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_entry_increases_the_stock(): void
    {
        $responsable = User::factory()->responsable()->create();
        $product = Product::factory()->withStock(40)->create();

        $this->actingAs($responsable)
            ->post(route('stock.entries.store'), [
                'product_id' => $product->id,
                'quantity' => 60,
                'reason' => 'Livraison fournisseur',
            ])
            ->assertRedirect(route('stock.entries.index'))
            ->assertSessionHas('success');

        $this->assertSame(100, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_entries', [
            'product_id' => $product->id,
            'quantity' => 60,
            'reason' => 'Livraison fournisseur',
            'user_id' => $responsable->id,
        ]);
    }

    #[DataProvider('invalidQuantities')]
    public function test_an_entry_with_a_non_positive_quantity_is_refused(mixed $quantity): void
    {
        $responsable = User::factory()->responsable()->create();
        $product = Product::factory()->withStock(10)->create();

        $this->actingAs($responsable)
            ->post(route('stock.entries.store'), [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_entries', 0);
    }

    public static function invalidQuantities(): array
    {
        return [
            'zéro' => [0],
            'moins cinq' => [-5],
            'moins dix' => [-10],
            'décimal' => [1.5],
            'texte' => ['abc'],
        ];
    }
}
