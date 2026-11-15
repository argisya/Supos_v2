<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('outgoing_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->dateTime('sold_at');
            $table->enum('payment_status', ['lunas', 'tempo'])->default('lunas');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('box_qty');
            $table->unsignedInteger('total_pcs');

            // Snapshot HPP saat transaksi (historical lock)
            $table->decimal('hpp_per_box', 15, 2);
            $table->decimal('sell_per_box', 15, 2);
            $table->decimal('hpp_per_pcs', 15, 2);
            $table->decimal('sell_per_pcs', 15, 2);

            $table->decimal('subtotal', 15, 2);      // omzet = sell_per_box * box_qty
            $table->decimal('total_hpp', 15, 2);     // hpp_per_box * box_qty
            $table->decimal('profit', 15, 2);        // subtotal - total_hpp

            $table->timestamps();

            $table->index(['payment_status']);
            $table->index(['sold_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('outgoing_transactions'); }
};