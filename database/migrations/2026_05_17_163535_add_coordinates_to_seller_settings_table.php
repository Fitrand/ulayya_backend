<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_settings', function (Blueprint $table) {
            $table->decimal('shop_latitude', 10, 7)->nullable()->after('shop_address');
            $table->decimal('shop_longitude', 10, 7)->nullable()->after('shop_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('seller_settings', function (Blueprint $table) {
            $table->dropColumn(['shop_latitude', 'shop_longitude']);
        });
    }
};

