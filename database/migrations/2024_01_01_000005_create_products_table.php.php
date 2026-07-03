<?php
// database/migrations/2024_01_01_000005_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('symbol');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->index();
            $table->string('barcode')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['inventory', 'service', 'non_inventory'])->default('inventory');
            $table->decimal('selling_price', 20, 4)->default(0);
            $table->decimal('purchase_price', 20, 4)->default(0);
            $table->decimal('hpp', 20, 4)->default(0);
            $table->decimal('min_stock', 20, 4)->default(0);
            $table->decimal('max_stock', 20, 4)->default(0);
            $table->decimal('opening_stock', 20, 4)->default(0);
            $table->string('image')->nullable();
            $table->boolean('is_sold')->default(true);
            $table->boolean('is_purchased')->default(true);
            $table->boolean('track_inventory')->default(true);
            $table->foreignId('sales_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('purchase_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('inventory_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('cogs_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->enum('costing_method', ['fifo', 'average', 'lifo'])->default('average');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('price_level')->default('retail');
            $table->decimal('price', 20, 4)->default(0);
            $table->decimal('min_qty', 20, 4)->default(1);
            $table->timestamps();
        });

        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('avg_cost', 20, 4)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('warehouses');
    }
};