<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();

            $table->json('name'); // ar/he/en
            $table->string('code', 10)->unique(); // ILS, JOD, AED
            $table->string('symbol', 20); // ₪, د.أ, د.إ
            $table->string('country_code', 10)->nullable();

            // سعر التحويل مقارنة بالعملة الافتراضية
            // مثال: إذا العملة الأساسية ILS، نخزن كم تساوي 1 ILS بالعملة الأخرى
            $table->decimal('exchange_rate', 15, 6)->default(1);

            $table->string('symbol_position')->default('before'); // before / after
            $table->unsignedTinyInteger('decimal_places')->default(2);

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};