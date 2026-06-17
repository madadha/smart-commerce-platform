<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('type')->default('internal');
            $table->text('note');
            $table->boolean('is_pinned')->default(false);

            $table->timestamps();

            $table->index(['order_id', 'created_at']);
            $table->index(['order_id', 'is_pinned']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_notes');
    }
};
