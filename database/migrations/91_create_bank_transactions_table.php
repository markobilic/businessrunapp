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
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('nalog_korisnik')->nullable();
            $table->string('mesto')->nullable();
            $table->string('vas_broj_naloga')->nullable();
            $table->string('broj_racuna_primaoca_posiljaoca')->nullable();
            $table->text('opis')->nullable();
            $table->string('sifra_placanja')->nullable();
            $table->text('sifra_placanja_opis')->nullable();
            $table->decimal('duguje', 15, 2)->nullable();
            $table->decimal('potrazuje', 15, 2)->nullable();
            $table->decimal('potrazuje_copy', 15, 2)->nullable();
            $table->string('model_zaduzenja_odobrenja')->nullable();
            $table->string('poziv_na_broj_zaduzenja_odobrenja')->nullable();
            $table->string('model_korisnika')->nullable();
            $table->string('poziv_na_broj_korisnika')->nullable();
            $table->string('broj_za_reklamaciju')->nullable();
            $table->string('referenca')->nullable();
            $table->text('objasnjenje')->nullable();
            $table->date('datum_valute')->nullable();
            $table->integer('broj_izvoda')->nullable();
            $table->date('datum_izvoda')->nullable();
            $table->boolean('approved')->default(false);
            $table->foreignId('reservation_id')->nullable()->constrained('reservations');   
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
        Schema::dropIfExists('bank_transactions');
    }
};
