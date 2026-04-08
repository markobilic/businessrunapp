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
        Schema::create('runner_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('runner_id')->nullable()->constrained('runners');   
            $table->foreignId('reservation_id')->constrained('reservations');   
            $table->integer('spot')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('runner_reservations');
    }
};
