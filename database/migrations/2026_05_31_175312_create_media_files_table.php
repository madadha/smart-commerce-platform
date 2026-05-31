<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();

            $table->string('disk')->default('public');
            $table->string('path');

            $table->string('type')->default('image'); // image, video, document, audio, file, other
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->json('title')->nullable();
            $table->json('alt_text')->nullable();
            $table->json('description')->nullable();

            $table->string('dominant_color', 20)->nullable();

            $table->json('ai_generated_alt')->nullable();
            $table->json('metadata')->nullable();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};