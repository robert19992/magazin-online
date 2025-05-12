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
            $table->boolean('idoc_order_generated')->default(false);
            $table->boolean('idoc_delivery_generated')->default(false);
            $table->timestamp('idoc_order_generated_at')->nullable();
            $table->timestamp('idoc_delivery_generated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'idoc_order_generated', 
                'idoc_delivery_generated', 
                'idoc_order_generated_at', 
                'idoc_delivery_generated_at'
            ]);
        });
    }
};
