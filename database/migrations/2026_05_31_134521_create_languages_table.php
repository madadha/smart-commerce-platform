<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();

            $table->string('name');              // Arabic, Hebrew, English
            $table->string('native_name');       // العربية, עברית, English
            $table->string('code', 10)->unique(); // ar, he, en
            $table->string('direction', 3)->default('ltr'); // rtl / ltr

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};