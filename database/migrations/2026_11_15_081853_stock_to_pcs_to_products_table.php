<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock_pcs')->default(0)->after('box_pcs_ratio');
        });

        // Backfill stok dari riwayat incoming yang sudah ada
        DB::statement('
            UPDATE products
            SET stock_pcs = (
                SELECT COALESCE(SUM(total_pcs), 0)
                FROM incoming_transactions
                WHERE incoming_transactions.product_id = products.id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock_pcs');
        });
    }
};