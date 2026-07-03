<?php
// database/migrations/2024_01_01_000011_create_stock_adjustments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('number')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->enum('type', ['increase', 'decrease', 'opname'])->default('opname');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_adjustment_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('qty_system', 20, 4)->default(0);
            $table->decimal('qty_actual', 20, 4)->default(0);
            $table->decimal('qty_difference', 20, 4)->default(0);
            $table->decimal('unit_cost', 20, 4)->default(0);
            $table->decimal('total_cost', 20, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('number')->unique();
            $table->date('date');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('quantity', 20, 4)->default(0);
            $table->decimal('unit_cost', 20, 4)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
    }
};