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
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_private')->default(true);

                $table->timestamps();

                $table->index(['order_id', 'created_at']);
                $table->index('user_id');
                $table->index('is_private');
            });

            return;
        }

        Schema::table('order_attachments', function (Blueprint $table) {
            if (! Schema::hasColumn('order_attachments', 'size_bytes')) {
                $table->unsignedBigInteger('size_bytes')
                    ->nullable()
                    ->after('mime_type');
            }

            if (! Schema::hasColumn('order_attachments', 'file_size')) {
                $table->unsignedBigInteger('file_size')
                    ->nullable()
                    ->after('size_bytes');
            }

            if (! Schema::hasColumn('order_attachments', 'is_private')) {
                $table->boolean('is_private')
                    ->default(true)
                    ->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('order_attachments', 'size_bytes')) {
                $table->dropColumn('size_bytes');
            }

            if (Schema::hasColumn('order_attachments', 'file_size')) {
                $table->dropColumn('file_size');
            }

            if (Schema::hasColumn('order_attachments', 'is_private')) {
                $table->dropColumn('is_private');
            }
        });
    }
};