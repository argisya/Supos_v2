<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('outgoing_transactions', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('profit');
            $table->index('due_date');
        });
    }
    public function down(): void {
        Schema::table('outgoing_transactions', function (Blueprint $table) {
            $table->dropIndex(['due_date']);
            $table->dropColumn('paid_amount');
        });
    }
};