<?php
// database/migrations/2024_01_01_000004_create_contacts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['customer', 'vendor', 'both']);
            $table->timestamps();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_group_id')->nullable()->constrained()->onDelete('set null');
            $table->string('code')->index();
            $table->string('name');
            $table->string('alias')->nullable();
            $table->enum('type', ['customer', 'vendor', 'both', 'employee']);
            $table->string('npwp')->nullable();
            $table->string('pkp_number')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_province')->nullable();
            $table->string('billing_postal_code')->nullable();
            $table->string('billing_country')->default('Indonesia');
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('contact_person')->nullable();
            $table->decimal('credit_limit', 20, 4)->default(0);
            $table->integer('payment_term')->default(0); // days
            $table->string('currency_code')->default('IDR');
            $table->foreignId('receivable_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('payable_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->decimal('opening_balance', 20, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('contact_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Indonesia');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_addresses');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('contact_groups');
    }
};