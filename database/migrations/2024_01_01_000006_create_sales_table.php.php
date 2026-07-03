<?php
// database/migrations/2024_01_01_000006_create_sales_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('number')->unique();
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'partial', 'delivered', 'invoiced', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('subtotal', 20, 4)->default(0);
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('shipping_cost', 20, 4)->default(0);
            $table->decimal('total', 20, 4)->default(0);
            $table->string('currency_code')->default('IDR');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('unit_price', 20, 4)->default(0);
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('tax_percent', 8, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('total', 20, 4)->default(0);
            $table->decimal('delivered_qty', 20, 4)->default(0);
            $table->decimal('invoiced_qty', 20, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('number')->unique();
            $table->date('date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->text('shipping_address')->nullable();
            $table->decimal('subtotal', 20, 4)->default(0);
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('shipping_cost', 20, 4)->default(0);
            $table->decimal('total', 20, 4)->default(0);
            $table->decimal('paid_amount', 20, 4)->default(0);
            $table->decimal('remaining_amount', 20, 4)->default(0);
            $table->string('currency_code')->default('IDR');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->foreignId('ar_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_item_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('unit_price', 20, 4)->default(0);
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('tax_percent', 8, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('total', 20, 4)->default(0);
            $table->decimal('hpp', 20, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sales_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('number')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('amount', 20, 4)->default(0);
            $table->string('currency_code')->default('IDR');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_receipt_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 20, 4)->default(0);
            $table->decimal('discount', 20, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('sales_order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('number')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->text('shipping_address')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_item_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
        Schema::dropIfExists('sales_receipt_items');
        Schema::dropIfExists('sales_receipts');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};