<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_attachments')) {
            Schema::create('order_attachments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->string('title')->nullable();
                $table->string('original_name')->nullable();
                $table->string('file_path');
                $table->string('disk')->default('public');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->text('notes')->nullable();

                $table->timestamps();

                $table->index(['order_id', 'created_at']);
                $table->index('user_id');
            });

            return;
        }

        Schema::table('order_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('order_attachments', 'order_id')) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('order_attachments', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('order_id')
                    ->constrained()
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('order_attachments', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('order_attachments', 'original_name')) {
                $table->string('original_name')->nullable()->after('title');
            }

            if (! Schema::hasColumn('order_attachments', 'file_path')) {
                $table->string('file_path')->nullable()->after('original_name');
            }

            if (! Schema::hasColumn('order_attachments', 'disk')) {
                $table->string('disk')->default('public')->after('file_path');
            }

            if (! Schema::hasColumn('order_attachments', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('disk');
            }

            if (! Schema::hasColumn('order_attachments', 'file_size')) {
                $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            }

            if (! Schema::hasColumn('order_attachments', 'notes')) {
                $table->text('notes')->nullable()->after('file_size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('order_attachments', 'order_id')) {
                $table->dropConstrainedForeignId('order_id');
            }

            if (Schema::hasColumn('order_attachments', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            foreach (['title', 'original_name', 'file_path', 'disk', 'mime_type', 'file_size', 'notes'] as $column) {
                if (Schema::hasColumn('order_attachments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};