<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('outgoing_transactions', 'paid_amount')) {
            Schema::table('outgoing_transactions', function (Blueprint $table) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('profit');
            });

            // Sinkronkan transaksi lama yang berstatus lunas
            DB::table('outgoing_transactions')
                ->where('payment_status', 'lunas')
                ->update(['paid_amount' => DB::raw('subtotal')]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('outgoing_transactions', 'paid_amount')) {
            Schema::table('outgoing_transactions', function (Blueprint $table) {
                $table->dropColumn('paid_amount');
            });
        }
    }
};