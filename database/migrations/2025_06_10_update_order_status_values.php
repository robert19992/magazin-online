<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificăm tipul coloanei status pentru a permite noile valori
        // Mai întâi eliminăm constrângerea enum existentă
        Schema::table('orders', function (Blueprint $table) {
            // Laravel/MySQL nu are o modalitate directă de a modifica un ENUM
            // Așa că vom crea o coloană temporară, vom muta datele și apoi o vom redenumi
            $table->string('status_temp')->default('pending')->after('status');
        });
        
        // Transferăm datele din status în status_temp
        DB::table('orders')->update([
            'status_temp' => DB::raw('status')
        ]);
        
        // Eliminăm vechea coloană status
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        // Adăugăm noua coloană status cu valorile actualizate
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'delivered', 'processing', 'completed', 'cancelled'])
                  ->default('pending')
                  ->after('status_temp');
        });
        
        // Transferăm datele din status_temp în noua coloană status
        // Cu mapare de la valorile vechi la cele noi
        DB::table('orders')->where('status_temp', 'processing')->update([
            'status' => 'active'
        ]);
        
        DB::table('orders')->where('status_temp', 'completed')->update([
            'status' => 'delivered'
        ]);
        
        // Pentru celelalte statusuri, transferăm așa cum sunt
        DB::table('orders')->where('status_temp', 'pending')->update([
            'status' => 'pending'
        ]);
        
        DB::table('orders')->where('status_temp', 'cancelled')->update([
            'status' => 'cancelled'
        ]);
        
        // Eliminăm coloana temporară
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status_temp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Refacem procesul invers
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status_temp')->default('pending')->after('status');
        });
        
        // Transferăm datele din status în status_temp, cu mapare inversă
        DB::table('orders')->where('status', 'active')->update([
            'status_temp' => 'processing'
        ]);
        
        DB::table('orders')->where('status', 'delivered')->update([
            'status_temp' => 'completed'
        ]);
        
        // Pentru celelalte statusuri, transferăm așa cum sunt
        DB::table('orders')->where('status', 'pending')->update([
            'status_temp' => 'pending'
        ]);
        
        DB::table('orders')->where('status', 'cancelled')->update([
            'status_temp' => 'cancelled'
        ]);
        
        // Eliminăm vechea coloană status
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        // Adăugăm noua coloană status cu valorile inițiale
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])
                  ->default('pending')
                  ->after('status_temp');
        });
        
        // Transferăm datele din status_temp în status
        DB::table('orders')->update([
            'status' => DB::raw('status_temp')
        ]);
        
        // Eliminăm coloana temporară
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status_temp');
        });
    }
}; 