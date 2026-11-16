<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incoming_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->dateTime('paid_at');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('Transfer Bank');
            $table->string('account')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('paid_at');
        });
    }
    public function down(): void { Schema::dropIfExists('supplier_payments'); }
};