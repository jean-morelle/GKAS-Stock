<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Une catégorie contenant des produits ne peut pas être supprimée.
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name', 150)->index();
            $table->string('reference', 50)->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('unit', 20);
            // Le stock n'est jamais saisi directement : il évolue uniquement via les mouvements.
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('alert_threshold')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
