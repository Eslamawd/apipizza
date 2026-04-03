<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            if (!Schema::hasColumn('menus', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('restaurant_id');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('menu_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'discount_percentage')) {
                $table->dropColumn('discount_percentage');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'discount_percentage')) {
                $table->dropColumn('discount_percentage');
            }
        });
    }
};
