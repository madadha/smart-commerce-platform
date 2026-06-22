<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->text('checkout_url')->nullable()->after('provider_reference');
            $table->dateTime('checkout_expires_at')->nullable()->after('checkout_url');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['checkout_url', 'checkout_expires_at']);
        });
    }
};
