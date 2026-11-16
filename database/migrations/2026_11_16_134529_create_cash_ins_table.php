<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cash_ins', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                  // KM-2025/11/001 / KM-NON-042
            $table->string('category', 40);                    // penjualan_tunai | pelunasan_piutang | refund_supplier | ...
            $table->string('account', 20);                     // bca | mandiri | kas_tunai
            $table->decimal('amount', 15, 2);
            $table->dateTime('received_at');
            $table->nullableMorphs('source');                  // source_type + source_id (optional)
            $table->string('reference_no')->nullable();        // no invoice / no PO
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->index(['category']);
            $table->index(['account']);
            $table->index(['received_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('cash_ins'); }
};