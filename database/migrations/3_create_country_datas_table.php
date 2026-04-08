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
        Schema::create('country_datas', function (Blueprint $table) {
            $table->id();
            $table->string('short_name');
            $table->string('long_name');
            $table->string('currency')->nullable()->default(null);
            $table->string('vat_label')->nullable()->default(null);
            $table->string('vat_percent')->nullable()->default(null);        
            $table->string('language');           
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_datas');
    }
};
