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
        Schema::create('kancelarka_responses', function (Blueprint $table) {
            $table->id();
            $table->longText('sent_data');
            $table->longText('response');
            $table->foreignId('reservation_id')->constrained('reservations');   
            $table->foreignId('bank_transaction_id')->nullable()->constrained('bank_transactions');  
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kancelarka_responses');
    }
};
