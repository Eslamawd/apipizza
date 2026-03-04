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
            if (!Schema::hasColumn('orders', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('orders', 'tip_percentage')) {
                $table->decimal('tip_percentage', 5, 2)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('orders', 'tips')) {
                $table->decimal('tips', 10, 2)->nullable()->after('tip_percentage');
            }
            if (!Schema::hasColumn('orders', 'scheduled_date')) {
                $table->date('scheduled_date')->nullable()->after('order_type');
            }
            if (!Schema::hasColumn('orders', 'scheduled_time')) {
                $table->time('scheduled_time')->nullable()->after('scheduled_date');
            }
            if (!Schema::hasColumn('orders', 'scheduled_for')) {
                $table->dateTime('scheduled_for')->nullable()->after('scheduled_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'customer_name',
                'tip_percentage',
                'tips',
                'scheduled_date',
                'scheduled_time',
                'scheduled_for',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
