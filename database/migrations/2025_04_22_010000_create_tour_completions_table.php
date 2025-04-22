<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tour_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->integer('calendars_sold')->default(0);
            
            // Billets
            $table->integer('tickets_5')->default(0)->comment('Billets de 5€');
            $table->integer('tickets_10')->default(0)->comment('Billets de 10€');
            $table->integer('tickets_20')->default(0)->comment('Billets de 20€');
            $table->integer('tickets_50')->default(0)->comment('Billets de 50€');
            $table->integer('tickets_100')->default(0)->comment('Billets de 100€');
            $table->integer('tickets_200')->default(0)->comment('Billets de 200€');
            $table->integer('tickets_500')->default(0)->comment('Billets de 500€');
            
            // Pièces
            $table->integer('coins_1c')->default(0)->comment('Pièces de 1 centime');
            $table->integer('coins_2c')->default(0)->comment('Pièces de 2 centimes');
            $table->integer('coins_5c')->default(0)->comment('Pièces de 5 centimes');
            $table->integer('coins_10c')->default(0)->comment('Pièces de 10 centimes');
            $table->integer('coins_20c')->default(0)->comment('Pièces de 20 centimes');
            $table->integer('coins_50c')->default(0)->comment('Pièces de 50 centimes');
            $table->integer('coins_1e')->default(0)->comment('Pièces de 1€');
            $table->integer('coins_2e')->default(0)->comment('Pièces de 2€');
            
            // Chèques
            $table->integer('check_count')->default(0)->comment('Nombre de chèques');
            $table->decimal('check_total_amount', 10, 2)->default(0)->comment('Montant total des chèques');
            
            // Champs pour stocker les montants individuels des chèques (JSON)
            $table->json('check_amounts')->nullable()->comment('Montants individuels des chèques');
            
            $table->decimal('total_amount', 10, 2)->default(0)->comment('Montant total collecté');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_completions');
    }
};