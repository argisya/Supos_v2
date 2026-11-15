<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('incoming_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('no_faktur')->unique();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->date('received_at');
            $table->enum('payment_status', ['lunas', 'tempo'])->default('lunas');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('box_qty');
            $table->unsignedInteger('total_pcs');
            $table->decimal('price_per_box', 15, 2);
            $table->decimal('price_per_pcs', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('incoming_transactions'); }
};