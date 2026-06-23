<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (! Schema::hasColumn('countries', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->nullable()->default(0)->after('currency_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (Schema::hasColumn('countries', 'tax_rate')) {
                $table->dropColumn('tax_rate');
            }
        });
    }
};
