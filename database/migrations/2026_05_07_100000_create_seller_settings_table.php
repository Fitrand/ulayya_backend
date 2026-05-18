<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_settings', function (Blueprint $table) {
            $table->id();
            $table->string('shop_name')->nullable();
            $table->text('shop_description')->nullable();
            $table->string('shop_email')->nullable();
            $table->string('shop_phone', 30)->nullable();
            $table->text('shop_address')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('order_notifications')->default(true);
            $table->boolean('low_stock_notifications')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_settings');
    }
};