<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_reminders')) {
            Schema::create('order_reminders', function (Blueprint $table) {
                $table->id();

                $table->foreignId('order_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained()
                    ->nullOnDelete();

                $table->foreignId('assigned_to_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('title');
                $table->text('notes')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('remind_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->boolean('is_private')->default(true);

                $table->timestamps();

                $table->index(['order_id', 'remind_at']);
                $table->index('user_id');
                $table->index('assigned_to_user_id');
                $table->index('status');
                $table->index('is_private');
            });

            return;
        }

        Schema::table('order_reminders', function (Blueprint $table) {
            if (! Schema::hasColumn('order_reminders', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('order_reminders', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('order_reminders', 'assigned_to_user_id')) {
                $table->foreignId('assigned_to_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('order_reminders', 'title')) {
                $table->string('title')->nullable()->after('assigned_to_user_id');
            }

            if (! Schema::hasColumn('order_reminders', 'notes')) {
                $table->text('notes')->nullable()->after('title');
            }

            if (! Schema::hasColumn('order_reminders', 'status')) {
                $table->string('status')->default('pending')->after('notes');
            }

            if (! Schema::hasColumn('order_reminders', 'remind_at')) {
                $table->timestamp('remind_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('order_reminders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('remind_at');
            }

            if (! Schema::hasColumn('order_reminders', 'is_private')) {
                $table->boolean('is_private')->default(true)->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_reminders');
    }
};
