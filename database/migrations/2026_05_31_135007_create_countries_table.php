<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();

            $table->json('name');
            $table->string('code', 10)->unique();

            $table->unsignedBigInteger('currency_id')->nullable();

            $table->string('phone_code', 10)->nullable();
            $table->string('flag', 255)->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->nullOnDelete();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};