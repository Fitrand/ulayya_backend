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
        Schema::table('seller_settings', function (Blueprint $table) {
            $table->string('promo_banner_image')->nullable();
            $table->string('promo_banner_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_settings', function (Blueprint $table) {
            $table->dropColumn(['promo_banner_image', 'promo_banner_text']);
        });
    }
};
