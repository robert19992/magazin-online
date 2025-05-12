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
        Schema::table('products', function (Blueprint $table) {
            $table->date('market_date')->nullable();
            $table->dropColumn('category');
            $table->dropColumn('specifications');
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('market_date');
            $table->string('category')->nullable();
            $table->text('specifications')->nullable();
            $table->boolean('is_active')->default(false);
        });
    }
};
