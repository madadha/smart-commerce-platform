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
                    $table->string('slug')->nullable()->after('name');
                }

                if (! Schema::hasColumn('brands', 'logo')) {
                    $table->string('logo')->nullable()->after('slug');
                }

                if (! Schema::hasColumn('brands', 'banner_image')) {
                    $table->string('banner_image')->nullable()->after('logo');
                }

                if (! Schema::hasColumn('brands', 'description')) {
                    $table->json('description')->nullable()->after('banner_image');
                }

                if (! Schema::hasColumn('brands', 'website_url')) {
                    $table->string('website_url')->nullable()->after('description');
                }

                if (! Schema::hasColumn('brands', 'seo_title')) {
                    $table->json('seo_title')->nullable()->after('website_url');
                }

                if (! Schema::hasColumn('brands', 'seo_description')) {
                    $table->json('seo_description')->nullable()->after('seo_title');
                }

                if (! Schema::hasColumn('brands', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('seo_description');
                }

                if (! Schema::hasColumn('brands', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('is_active');
                }
            });
        }

        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (! Schema::hasColumn('companies', 'name')) {
                    $table->json('name')->nullable();
                }

                if (! Schema::hasColumn('companies', 'slug')) {
                    $table->string('slug')->nullable()->after('name');
                }

                if (! Schema::hasColumn('companies', 'type')) {
                    $table->string('type')->default('supplier')->after('slug');
                }

                if (! Schema::hasColumn('companies', 'logo')) {
                    $table->string('logo')->nullable()->after('type');
                }

                if (! Schema::hasColumn('companies', 'email')) {
                    $table->string('email')->nullable()->after('logo');
                }

                if (! Schema::hasColumn('companies', 'phone')) {
                    $table->string('phone')->nullable()->after('email');
                }

                if (! Schema::hasColumn('companies', 'website')) {
                    $table->string('website')->nullable()->after('phone');
                }

                if (! Schema::hasColumn('companies', 'country_id')) {
                    $table->unsignedBigInteger('country_id')->nullable()->after('website');
                }

                if (! Schema::hasColumn('companies', 'notes')) {
                    $table->text('notes')->nullable()->after('country_id');
                }

                if (! Schema::hasColumn('companies', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('notes');
                }

                if (! Schema::hasColumn('companies', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        //
    }
};