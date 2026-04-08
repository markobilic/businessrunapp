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
        Schema::create('runners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->bigInteger('pin')->unique();
            $table->string('phone');
            $table->string('shirt_size')->nullable();
            $table->foreignId('shirt_size_id')->nullable()->constrained('shirt_sizes');   
            $table->foreignId('socks_size_id')->nullable()->constrained('socks_sizes');   
            $table->date('date_of_birth');
            $table->enum('sex', ['Male', 'Female']);
            $table->string('work_position')->nullable();
            $table->string('work_sector')->nullable();
            $table->string('week_running')->nullable();
            $table->string('longest_race')->nullable();
            $table->foreignId('work_position_id')->nullable()->constrained('work_positions');   
            $table->foreignId('work_sector_id')->nullable()->constrained('work_sectors');        
            $table->foreignId('week_running_id')->nullable()->constrained('week_runnings'); 
            $table->foreignId('longest_race_id')->nullable()->constrained('longest_races');        
            $table->foreignId('captain_id')->constrained('captains');  
            $table->string('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('runners');
    }
};
