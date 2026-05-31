<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (! Schema::hasColumn('brands', 'name')) {
                    $table->json('name')->nullable();
                }

                if (! Schema::hasColumn('brands', 'slug')) {
                    $table->string('slug')->nullable();
                }

                if (! Schema::hasColumn('brands', 'logo')) {
                    $table->string('logo')->nullable();
                }

                if (! Schema::hasColumn('brands', 'banner_image')) {
                    $table->string('banner_image')->nullable();
                }

                if (! Schema::hasColumn('brands', 'description')) {
                    $table->json('description')->nullable();
                }

                if (! Schema::hasColumn('brands', 'website_url')) {
                    $table->string('website_url')->nullable();
                }

                if (! Schema::hasColumn('brands', 'seo_title')) {
                    $table->json('seo_title')->nullable();
                }

                if (! Schema::hasColumn('brands', 'seo_description')) {
                    $table->json('seo_description')->nullable();
                }

                if (! Schema::hasColumn('brands', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }

                if (! Schema::hasColumn('brands', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0);
                }
            });
        }

        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (! Schema::hasColumn('companies', 'name')) {
                    $table->json('name')->nullable();
                }

                if (! Schema::hasColumn('companies', 'slug')) {
                    $table->string('slug')->nullable();
                }

                if (! Schema::hasColumn('companies', 'type')) {
                    $table->string('type')->default('supplier');
                }

                if (! Schema::hasColumn('companies', 'logo')) {
                    $table->string('logo')->nullable();
                }

                if (! Schema::hasColumn('companies', 'email')) {
                    $table->string('email')->nullable();
                }

                if (! Schema::hasColumn('companies', 'phone')) {
                    $table->string('phone')->nullable();
                }

                if (! Schema::hasColumn('companies', 'website')) {
                    $table->string('website')->nullable();
                }

                if (! Schema::hasColumn('companies', 'country_id')) {
                    $table->unsignedBigInteger('country_id')->nullable();
                }

                if (! Schema::hasColumn('companies', 'notes')) {
                    $table->text('notes')->nullable();
                }

                if (! Schema::hasColumn('companies', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }

                if (! Schema::hasColumn('companies', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};