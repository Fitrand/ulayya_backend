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
        Schema::table('addresses', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('postal_code');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('shipping_address');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
