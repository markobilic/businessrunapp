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
        Schema::create('captain_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('captain_id')->constrained('captains');   
            $table->string('company_name');
            $table->string('city');
            $table->string('address');   
            $table->string('postal_code');        
            $table->string('phone_number');
            $table->string('pin');
            $table->string('jbkjs')->default(null)->nullable();
            $table->string('identification_number');            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captain_addresses');
    }
};
