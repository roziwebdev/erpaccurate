<?php
// database/migrations/2024_01_01_000003_create_accounts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->boolean('is_debit_normal')->default(true);
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->string('code')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('sub_type', [
                'cash', 'bank', 'receivable', 'inventory', 'fixed_asset', 'other_asset',
                'payable', 'short_term_loan', 'long_term_loan', 'other_liability',
                'capital', 'retained_earnings', 'other_equity',
                'sales', 'other_revenue',
                'cogs', 'operating_expense', 'other_expense'
            ])->nullable();
            $table->decimal('opening_balance', 20, 4)->default(0);
            $table->decimal('current_balance', 20, 4)->default(0);
            $table->boolean('is_header')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->integer('level')->default(1);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('account_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->decimal('jan', 20, 4)->default(0);
            $table->decimal('feb', 20, 4)->default(0);
            $table->decimal('mar', 20, 4)->default(0);
            $table->decimal('apr', 20, 4)->default(0);
            $table->decimal('may', 20, 4)->default(0);
            $table->decimal('jun', 20, 4)->default(0);
            $table->decimal('jul', 20, 4)->default(0);
            $table->decimal('aug', 20, 4)->default(0);
            $table->decimal('sep', 20, 4)->default(0);
            $table->decimal('oct', 20, 4)->default(0);
            $table->decimal('nov', 20, 4)->default(0);
            $table->decimal('dec', 20, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_budgets');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_categories');
    }
};