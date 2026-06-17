<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_activities')) {
            Schema::create('order_activities', function (Blueprint $table) {
                $table->id();

                $table->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('type')->default('general');
                $table->string('title')->nullable();
                $table->text('description')->nullable();

                $table->string('old_status')->nullable();
                $table->string('new_status')->nullable();

                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();

                $table->json('metadata')->nullable();
                $table->timestamp('occurred_at')->nullable();

                $table->timestamps();

                $table->index(['order_id', 'occurred_at']);
                $table->index(['type', 'created_at']);
                $table->index(['subject_type', 'subject_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_activities');
    }
};
