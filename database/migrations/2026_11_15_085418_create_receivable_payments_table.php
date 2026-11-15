<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outgoing_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->dateTime('paid_at');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->default('Transfer Bank'); // Transfer Bank | Kas Tunai
            $table->string('account')->nullable();      // BCA, Mandiri, Kas Toko
            $table->string('reference_no')->nullable(); // No. referensi transfer
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['paid_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('receivable_payments'); }
};