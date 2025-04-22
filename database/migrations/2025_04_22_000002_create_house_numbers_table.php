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
        Schema::create('house_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->foreignId('street_id')->constrained()->onDelete('cascade');
            $table->string('number');
            $table->enum('status', ['to_revisit', 'visited', 'skipped'])->default('to_revisit');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['tour_id', 'street_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('house_numbers');
    }
};