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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'delivery_fee')) {
                $table->decimal('delivery_fee', 8, 2)->nullable()->after('discount_percentage');
            }
            if (!Schema::hasColumn('orders', 'delivery_distance')) {
                $table->decimal('delivery_distance', 6, 2)->nullable()->after('delivery_fee');
            }
            if (!Schema::hasColumn('orders', 'tax')) {
                $table->decimal('tax', 8, 2)->nullable()->after('delivery_distance');
            }
            if (!Schema::hasColumn('orders', 'tips')) {
                $table->decimal('tips', 8, 2)->nullable()->after('tax');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_fee', 'delivery_distance', 'tax', 'tips']);
        });
    }
};
