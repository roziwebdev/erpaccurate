<?php
// database/migrations/2024_01_01_000007_create_purchases_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('number')->unique();
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->date('expected_date')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'partial', 'received', 'billed', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
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

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('unit_price', 20, 4)->default(0);
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('tax_percent', 8, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('total', 20, 4)->default(0);
            $table->decimal('received_qty', 20, 4)->default(0);
            $table->decimal('billed_qty', 20, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('number')->unique();
            $table->string('vendor_invoice_number')->nullable();
            $table->date('date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'posted', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 20, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('shipping_cost', 20, 4)->default(0);
            $table->decimal('total', 20, 4)->default(0);
            $table->decimal('paid_amount', 20, 4)->default(0);
            $table->decimal('remaining_amount', 20, 4)->default(0);
            $table->string('currency_code')->default('IDR');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->foreignId('ap_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('warehouse_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('unit_price', 20, 4)->default(0);
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->decimal('discount_amount', 20, 4)->default(0);
            $table->decimal('tax_percent', 8, 4)->default(0);
            $table->decimal('tax_amount', 20, 4)->default(0);
            $table->decimal('total', 20, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('purchase_payments', function (Blueprint $table) {
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

        Schema::create('purchase_payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 20, 4)->default(0);
            $table->decimal('discount', 20, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('receive_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('number')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('receive_item_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receive_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_item_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('unit_price', 20, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receive_item_details');
        Schema::dropIfExists('receive_items');
        Schema::dropIfExists('purchase_payment_items');
        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};