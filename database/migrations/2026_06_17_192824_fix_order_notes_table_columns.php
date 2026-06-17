<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_notes')) {
            Schema::create('order_notes', function (Blueprint $table) {
                $table->id();

                $table->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->text('note');
                $table->boolean('is_pinned')->default(false);
                $table->timestamps();

                $table->index(['order_id', 'created_at']);
                $table->index('is_pinned');
            });

            return;
        }

        Schema::table('order_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('order_notes', 'order_id')) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('order_notes', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('order_notes', 'note')) {
                $table->text('note')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('order_notes', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_notes', function (Blueprint $table) {
            if (Schema::hasColumn('order_notes', 'order_id')) {
                $table->dropConstrainedForeignId('order_id');
            }

            if (Schema::hasColumn('order_notes', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('order_notes', 'note')) {
                $table->dropColumn('note');
            }

            if (Schema::hasColumn('order_notes', 'is_pinned')) {
                $table->dropColumn('is_pinned');
            }
        });
    }
};