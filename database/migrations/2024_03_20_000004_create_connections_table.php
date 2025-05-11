<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('users');
            $table->foreignId('client_id')->constrained('users');
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('disconnected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['supplier_id', 'client_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
}; 