<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'requested_customer_type')) {
                $table->string('requested_customer_type')->nullable()->after('customer_type')->index();
            }

            if (! Schema::hasColumn('customers', 'customer_type_requested_at')) {
                $table->timestamp('customer_type_requested_at')->nullable()->after('requested_customer_type');
            }

            if (! Schema::hasColumn('customers', 'customer_type_approved_at')) {
                $table->timestamp('customer_type_approved_at')->nullable()->after('customer_type_requested_at');
            }

            if (! Schema::hasColumn('customers', 'customer_type_approved_by')) {
                $table->foreignId('customer_type_approved_by')
                    ->nullable()
                    ->after('customer_type_approved_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'customer_type_approved_by')) {
                $table->dropConstrainedForeignId('customer_type_approved_by');
            }

            if (Schema::hasColumn('customers', 'customer_type_approved_at')) {
                $table->dropColumn('customer_type_approved_at');
            }

            if (Schema::hasColumn('customers', 'customer_type_requested_at')) {
                $table->dropColumn('customer_type_requested_at');
            }

            if (Schema::hasColumn('customers', 'requested_customer_type')) {
                $table->dropColumn('requested_customer_type');
            }
        });
    }
};
