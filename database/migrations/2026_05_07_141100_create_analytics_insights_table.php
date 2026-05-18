<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_insights', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('metric', 100);
            $table->decimal('value', 14, 2)->default(0);
            $table->string('trend', 20)->default('stable');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_insights');
    }
};
