<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('storefront_slides')) {
            Schema::create('storefront_slides', function (Blueprint $table) {
                $table->id();
                $table->json('badge')->nullable();
                $table->json('title')->nullable();
                $table->json('description')->nullable();
                $table->string('image_path')->nullable();
                $table->json('primary_button_text')->nullable();
                $table->string('primary_button_url')->nullable();
                $table->json('secondary_button_text')->nullable();
                $table->string('secondary_button_url')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_slides');
    }
};
