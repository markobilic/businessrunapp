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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->foreignId('inventory_type_id')->nullable()->constrained('inventory_types');   
            $table->string('name');
            $table->char('abbreviation', 10)->nullable(); 
            $table->string('description');
            $table->integer('order')->nullable();  
            $table->foreignId('race_id')->constrained('races');   
            $table->boolean('active')->default(true)->nullable();
            $table->integer('max_value')->nullable();  
            $table->string('sub_type')->nullable();
            $table->foreignId('inventory_sub_type_id')->nullable()->constrained('inventory_sub_types');   
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
