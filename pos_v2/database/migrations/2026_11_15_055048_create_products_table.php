<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->index();
            $table->string('name');
            $table->string('variant')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('box_pcs_ratio')->default(1);
            $table->enum('status', ['active', 'archived'])->default('active')->index();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};