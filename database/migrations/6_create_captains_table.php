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
        Schema::create('captains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->string('company_name')->nullable();
            $table->string('city')->nullable();
            $table->string('team_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('pin')->unique();
            $table->string('jbkjs')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('address')->nullable();            
            $table->string('billing_company')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_pin')->nullable();
            $table->string('billing_jbkjs')->nullable();
            $table->string('billing_identification_number')->nullable();
            $table->foreignId('total_employee_type_id')->nullable()->constrained('total_employee_types');   
            $table->foreignId('company_type_id')->nullable()->constrained('company_types');   
            $table->foreignId('business_type_id')->nullable()->constrained('business_types');   
            $table->string('total_employees')->nullable();
            $table->string('company_type')->nullable();
            $table->string('business')->nullable();
            $table->text('custom_welcome')->nullable();
            $table->string('email')->unique()->nullable();
            $table->foreignId('organizer_id')->constrained('organizers');   
            $table->foreignId('user_id')->constrained('users');   
            $table->string('remember_token')->nullable();
            $table->string('postcode')->nullable();
            $table->string('billing_postcode')->nullable();
            $table->boolean('sponsor')->default(0);
            $table->boolean('partner')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captains');
    }
};
