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
            // Tambahkan kolom snap_token (nullable karena bisa jadi tokennya kosong sebelum di-generate)
            $table->string('snap_token')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Hapus kolom snap_token jika migration di-rollback
            $table->dropColumn('snap_token');
        });
    }
};