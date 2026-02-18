<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, make order_id nullable
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->change();
        });

        // Drop existing foreign key if it exists
        Schema::table('payments', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesDetail = $sm->listTableForeignKeys('payments');
            
            foreach ($indexesDetail as $foreignKey) {
                if ($foreignKey->getLocalColumns()[0] === 'order_id') {
                    $table->dropForeign(['order_id']);
                }
            }
        });

        // Add new foreign key with SET NULL
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexesDetail = $sm->listTableForeignKeys('payments');
            
            foreach ($indexesDetail as $foreignKey) {
                if ($foreignKey->getLocalColumns()[0] === 'order_id') {
                    $table->dropForeign(['order_id']);
                }
            }
            
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
        });
    }
};
