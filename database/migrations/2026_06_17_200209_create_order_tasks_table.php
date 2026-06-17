<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('order_tasks')) {
            Schema::create('order_tasks', function (Blueprint $table) {
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
                $table->text('description')->nullable();
                $table->string('status')->default('pending');
                $table->string('priority')->default('normal');
                $table->timestamp('due_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->boolean('is_private')->default(true);

                $table->timestamps();

                $table->index(['order_id', 'status']);
                $table->index(['order_id', 'due_at']);
                $table->index('assigned_to_user_id');
                $table->index('priority');
            });

            return;
        }

        Schema::table('order_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('order_tasks', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            }

            if (! Schema::hasColumn('order_tasks', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('order_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('order_tasks', 'assigned_to_user_id')) {
                $table->foreignId('assigned_to_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('order_tasks', 'title')) {
                $table->string('title')->nullable()->after('assigned_to_user_id');
            }

            if (! Schema::hasColumn('order_tasks', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (! Schema::hasColumn('order_tasks', 'status')) {
                $table->string('status')->default('pending')->after('description');
            }

            if (! Schema::hasColumn('order_tasks', 'priority')) {
                $table->string('priority')->default('normal')->after('status');
            }

            if (! Schema::hasColumn('order_tasks', 'due_at')) {
                $table->timestamp('due_at')->nullable()->after('priority');
            }

            if (! Schema::hasColumn('order_tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('due_at');
            }

            if (! Schema::hasColumn('order_tasks', 'is_private')) {
                $table->boolean('is_private')->default(true)->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tasks');
    }
};
