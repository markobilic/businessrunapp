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
        Schema::create('races', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('bill_prefix')->nullable();
            $table->string('name');
            $table->integer('startplaces');
            $table->integer('amount')->nullable()->default(null);
            $table->integer('turnover_startplaces')->nullable()->default(null);
            $table->integer('turnover')->nullable()->default(null);
            $table->dateTime('starting_date');
            $table->dateTime('application_start');
            $table->dateTime('application_end');
            $table->dateTime('order_end')->nullable()->default(null);      
            $table->foreignId('organizer_id')->constrained('organizers');
            $table->foreignId('user_id')->constrained('users');
            $table->boolean('locked')->default(false);
            $table->string('logo')->nullable()->default(null);
            $table->string('web')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('races');
    }
};
