<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->after('payment_number')->unique();
            }

            if (! Schema::hasColumn('payments', 'failure_code')) {
                $table->string('failure_code')->nullable()->after('provider_payload');
            }

            if (! Schema::hasColumn('payments', 'failure_message')) {
                $table->text('failure_message')->nullable()->after('failure_code');
            }
        });

        if (! Schema::hasTable('payment_webhook_events')) {
            Schema::create('payment_webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('provider');
                $table->string('event_id');
                $table->string('event_type')->nullable();
                $table->string('status')->default('pending');
                $table->json('payload');
                $table->dateTime('received_at');
                $table->dateTime('processed_at')->nullable();
                $table->dateTime('failed_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->unique(['provider', 'event_id']);
                $table->index(['status', 'received_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'idempotency_key')) {
                $table->dropUnique(['idempotency_key']);
                $table->dropColumn('idempotency_key');
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('payments', 'failure_code') ? 'failure_code' : null,
                Schema::hasColumn('payments', 'failure_message') ? 'failure_message' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
