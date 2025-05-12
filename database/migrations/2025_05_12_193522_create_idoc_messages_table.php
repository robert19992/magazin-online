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
        Schema::create('idoc_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('message_type'); // 'order', 'delivery'
            $table->string('direction'); // 'client_to_supplier', 'supplier_to_client'
            $table->string('file_path');
            $table->text('content')->nullable();
            $table->string('status')->default('pending'); // 'pending', 'processed', 'failed'
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idoc_messages');
    }
};
