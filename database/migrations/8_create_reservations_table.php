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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('captain_id')->constrained('captains');
            $table->foreignId('race_id')->constrained('races');
            $table->boolean('payment_status')->default(false);
            $table->boolean('locked')->default(false);
            $table->boolean('legal_entity')->default(true);
            $table->boolean('invoice_status')->default(false);
            $table->string('promo_code')->nullable()->default(null);
            $table->integer('reserved_places')->nullable()->default(null);
            $table->dateTime('payment_date')->nullable()->default(null);
            $table->date('locked_date')->nullable()->default(null);
            $table->dateTime('sent_email')->nullable()->default(null);
            $table->foreignId('captain_address_id')->nullable()->constrained('captain_addresses');
            $table->decimal('paid', 8, 2)->nullable()->default(null);
            $table->string('order_number')->nullable()->default(null);
            $table->string('extra_order_number')->nullable()->default(null);
            $table->string('base20')->nullable()->default(null);
            $table->date('extra_order_date')->nullable()->default(null);
            $table->boolean('crf')->default(false);
            $table->string('invoice_sufix')->nullable()->default(null);
            $table->string('sufix_final')->nullable()->default(null);
            $table->text('note')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
