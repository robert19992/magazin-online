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
            $table->string('part_number')->nullable()->after('sku');
            $table->string('manufacturer')->nullable()->after('description');
            $table->decimal('weight', 10, 2)->nullable()->after('manufacturer');
            
            // Redenumește câmpul name în cod_produs
            $table->renameColumn('name', 'cod_produs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('cod_produs', 'name');
            $table->dropColumn(['part_number', 'manufacturer', 'weight']);
        });
    }
};
