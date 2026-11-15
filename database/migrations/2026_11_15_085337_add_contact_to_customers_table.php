<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('pic_name')->nullable()->after('name');
            $table->string('phone')->nullable()->after('pic_name');
        });
    }
    public function down(): void {
        Schema::table('customers', fn (Blueprint $t) => $t->dropColumn(['pic_name', 'phone']));
    }
};