<?php
// database/migrations/2024_01_01_000010_create_fixed_assets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('useful_life')->default(0); // in months
            $table->decimal('depreciation_rate', 8, 4)->default(0);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'none'])->default('straight_line');
            $table->foreignId('asset_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('depreciation_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('accumulated_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 20, 4)->default(0);
            $table->decimal('salvage_value', 20, 4)->default(0);
            $table->decimal('book_value', 20, 4)->default(0);
            $table->decimal('accumulated_depreciation', 20, 4)->default(0);
            $table->integer('useful_life')->default(0);
            $table->decimal('depreciation_rate', 8, 4)->default(0);
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'none'])->default('straight_line');
            $table->enum('status', ['active', 'disposed', 'sold'])->default('active');
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_amount', 20, 4)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('journal_id')->nullable()->constrained()->onDelete('set null');
            $table->year('year');
            $table->tinyInteger('month');
            $table->decimal('amount', 20, 4)->default(0);
            $table->decimal('book_value_before', 20, 4)->default(0);
            $table->decimal('book_value_after', 20, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('asset_categories');
    }
};