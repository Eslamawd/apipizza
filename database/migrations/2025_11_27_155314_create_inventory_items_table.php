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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            
            // بيانات الصنف الأساسية
            $table->string('name');
            // توسيع قائمة الوحدات لتشمل الوزن، الحجم، والطول
            $table->enum('unit', ['kg', 'g', 'l', 'ml', 'm', 'cm', 'unit', 'box']);
            $table->float('quantity');
            $table->decimal('total_price', 10, 4)->comment('Total cost of the purchased quantity.'); // رفع الدقة إلى 4 للمبالغ الكبيرة
            
            // أسعار الوحدة (الوزن)
            $table->decimal('unit_price_per_kg', 10, 4)->nullable()->comment('Price per kilogram (Calculated).');
            $table->decimal('unit_price_per_gram', 10, 4)->nullable()->comment('Price per gram (Calculated).');

            // أسعار الوحدة (الحجم)
            $table->decimal('unit_price_per_liter', 10, 4)->nullable()->comment('Price per liter (Calculated).');
            $table->decimal('unit_price_per_ml', 10, 4)->nullable()->comment('Price per milliliter (Calculated).');

            // أسعار الوحدة (الطول)
            $table->decimal('unit_price_per_meter', 10, 4)->nullable()->comment('Price per meter (Calculated).');
            $table->decimal('unit_price_per_cm', 10, 4)->nullable()->comment('Price per centimeter (Calculated).');

            // العلاقات والتواريخ
            $table->unsignedBigInteger('restaurant_id');
            $table->date('received_at')->comment('Date the inventory was received or entered.');
            $table->date('expires_at')->nullable()->comment('Expiration date of the item.');

            // المفتاح الأجنبي
            $table->foreign('restaurant_id')->references('id')->on('restaurants')->onDelete('cascade');
            
            $table->timestamps();
            
            // إضافة مفتاح فريد لضمان عدم تكرار اسم الصنف لنفس المطعم
            $table->unique(['name', 'restaurant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};