<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'min_stock_box')) {
                $table->unsignedInteger('min_stock_box')->default(10)->after('box_pcs_ratio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'min_stock_box')) {
                $table->dropColumn('min_stock_box');
            }
        });
    }
};