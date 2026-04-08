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
        Schema::create('total_employee_types', function (Blueprint $table) {
            $table->id();
            $table->string('total_employee_type_name');
            $table->integer('min_employee');
            $table->integer('max_employee');
            $table->foreignId('organizer_id')->nullable()->constrained('organizers');   
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('total_employee_types');
    }
};
