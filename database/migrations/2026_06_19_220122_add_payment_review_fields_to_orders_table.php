<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_review_status')) {
                $table->string('payment_review_status')->default('pending')->after('payment_status');
            }

            if (! Schema::hasColumn('orders', 'payment_review_notes')) {
                $table->text('payment_review_notes')->nullable()->after('payment_review_status');
            }

            if (! Schema::hasColumn('orders', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_review_notes');
            }

            if (! Schema::hasColumn('orders', 'payment_reviewed_by')) {
                $table->foreignId('payment_reviewed_by')
                    ->nullable()
                    ->after('payment_reference')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'payment_reviewed_at')) {
                $table->timestamp('payment_reviewed_at')->nullable()->after('payment_reviewed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_reviewed_by')) {
                $table->dropConstrainedForeignId('payment_reviewed_by');
            }

            foreach ([
                'payment_review_status',
                'payment_review_notes',
                'payment_reference',
                'payment_reviewed_at',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
