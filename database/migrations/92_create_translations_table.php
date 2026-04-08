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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->longText('content')->nullable();
            $table->foreignId('organizer_id')->nullable()->constrained('organizers');
            $table->string('component')->nullable();
            $table->mediumText('eng')->nullable();
            $table->mediumText('srb')->nullable();
            $table->mediumText('mon')->nullable();
            $table->mediumText('cro')->nullable();
            $table->mediumText('alb')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
