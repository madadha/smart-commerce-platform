<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'priority')) {
                $table->string('priority')->default('normal')->after('payment_status')->index();
            }

            if (! Schema::hasColumn('orders', 'priority_reason')) {
                $table->text('priority_reason')->nullable()->after('priority');
            }

            if (! Schema::hasColumn('orders', 'priority_updated_by')) {
                $table->foreignId('priority_updated_by')
                    ->nullable()
                    ->after('priority_reason')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'prioritized_at')) {
                $table->timestamp('prioritized_at')->nullable()->after('priority_updated_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'priority_updated_by')) {
                $table->dropConstrainedForeignId('priority_updated_by');
            }

            foreach (['priority', 'priority_reason', 'prioritized_at'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
