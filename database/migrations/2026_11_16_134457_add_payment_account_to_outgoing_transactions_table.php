<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('outgoing_transactions', function (Blueprint $table) {
            // bca | mandiri | kas_tunai
            $table->string('payment_account', 20)->default('kas_tunai')->after('payment_status');
        });
    }
    public function down(): void {
        Schema::table('outgoing_transactions', fn (Blueprint $t) => $t->dropColumn('payment_account'));
    }
};