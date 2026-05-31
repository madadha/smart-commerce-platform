<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->json('name');
            $table->string('slug')->unique();

            $table->json('description')->nullable();

            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->string('banner_image')->nullable();

            $table->json('seo_title')->nullable();
            $table->json('seo_description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('parent_id');
            $table->index(['is_active', 'sort_order']);
            $table->index(['show_in_menu', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};