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
        Schema::create('organizers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name');
            $table->string('subdomain')->unique();
            $table->string('pin');
            $table->string('pin_other')->nullable()->default(null);
            $table->string('address');
            $table->string('city');
            $table->string('postcode');
            $table->string('country')->nullable();
            $table->foreignId('country_data_id')->nullable()->constrained('country_datas');               
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('website');
            $table->string('support_link');
            $table->string('tos_link');
            $table->string('currency')->nullable();
            $table->string('giro_account');
            $table->string('vat_label')->nullable();
            $table->string('vat_percent')->nullable();
            $table->string('logo')->nullable()->default(null);
            $table->string('invoice_signature')->nullable()->default(null);
            $table->string('logo_alt')->nullable()->default(null);
            $table->foreignId('user_id')->constrained('users');  
            $table->char('language', 3)->nullable();     
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
        Schema::dropIfExists('organizers');
    }
};
