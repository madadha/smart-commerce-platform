<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'checkout_url')) {
                $table->text('checkout_url')->nullable()->after('provider_reference');
            }

            if (! Schema::hasColumn('payments', 'checkout_expires_at')) {
                $table->dateTime('checkout_expires_at')->nullable()->after('checkout_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $columns = array_values(array_filter([
                Schema::hasColumn('payments', 'checkout_url') ? 'checkout_url' : null,
                Schema::hasColumn('payments', 'checkout_expires_at') ? 'checkout_expires_at' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
