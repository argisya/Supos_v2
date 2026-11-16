<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cash_outs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('category', 40);
            $table->string('account', 20);
            $table->decimal('amount', 15, 2);
            $table->dateTime('paid_at');
            $table->nullableMorphs('source');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
            $table->index('category');
            $table->index('account');
            $table->index('paid_at');
        });
    }
    public function down(): void { Schema::dropIfExists('cash_outs'); }
};