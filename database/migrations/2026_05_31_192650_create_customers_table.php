<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->string('customer_type')->default('regular');
            // regular, reseller, vip, company

            $table->string('status')->default('active');
            // active, inactive, blocked

            $table->string('first_name');
            $table->string('last_name')->nullable();

            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();

            $table->string('identity_number')->nullable();
            $table->date('birth_date')->nullable();

            $table->string('company_name')->nullable();
            $table->string('tax_number')->nullable();

            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->string('street')->nullable();
            $table->string('building')->nullable();
            $table->string('apartment')->nullable();
            $table->string('postal_code')->nullable();

            $table->text('address_notes')->nullable();
            $table->text('internal_notes')->nullable();

            $table->boolean('accepts_marketing')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['customer_type', 'status']);
            $table->index(['status', 'is_active']);
            $table->index(['country_id', 'city']);
            $table->index('phone');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};