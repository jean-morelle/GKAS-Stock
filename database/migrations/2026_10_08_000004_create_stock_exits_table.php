<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_exits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            // Technicien qui reçoit le matériel.
            $table->foreignId('technician_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('reason');
            $table->string('intervention')->nullable();
            $table->text('observation')->nullable();
            // Responsable qui enregistre l'opération.
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['technician_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_exits');
    }
};
