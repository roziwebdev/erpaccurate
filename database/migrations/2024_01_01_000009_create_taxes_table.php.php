<?php
// database/migrations/2024_01_01_000009_create_taxes_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->decimal('rate', 8, 4)->default(0);
            $table->enum('type', ['ppn', 'pph', 'custom'])->default('ppn');
            $table->foreignId('sales_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('purchase_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};