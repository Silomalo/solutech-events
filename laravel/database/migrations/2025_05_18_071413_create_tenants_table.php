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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('serial_number')->unique();
            $table->string('company_logo')->nullable();
            $table->integer('active_package')->nullable();
            $table->string('tenant_name')->unique();
            $table->string('tenant_domain')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('database_name')->unique();
            $table->string('database_host')->nullable();
            $table->string('database_port')->nullable();
            $table->string('database_username')->nullable();
            $table->string('database_password')->default('');
            $table->boolean('status')->default(0); //starts as inactive until verified
            $table->timestamp('account_activated_at')->nullable();
            $table->integer('account_activated_by')->nullable();
            $table->timestamp('account_deactivated_at')->nullable();
            $table->integer('account_deactivated_by')->nullable();
            $table->longText('description')->nullable();
            // additional fields
            $table->string('registration_no')->nullable();
            $table->string('kra_pin')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_title')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('legal_entity')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
