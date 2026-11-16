<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('incoming_transactions', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_status');
        });
        // Backfill: yang sudah lunas, paid_amount = subtotal
        DB::statement("UPDATE incoming_transactions SET paid_amount = subtotal WHERE payment_status = 'lunas'");
    }
    public function down(): void {
        Schema::table('incoming_transactions', fn (Blueprint $t) => $t->dropColumn('paid_amount'));
    }
};