<?php
// routes/web.php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\AccountController;
use App\Http\Controllers\Finance\JournalController;
use App\Http\Controllers\Finance\CashBankController;
use App\Http\Controllers\Finance\FixedAssetController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\Sales\SalesInvoiceController;
use App\Http\Controllers\Sales\SalesReceiptController;
use App\Http\Controllers\Sales\DeliveryOrderController;
use App\Http\Controllers\Purchase\PurchaseOrderController;
use App\Http\Controllers\Purchase\PurchaseInvoiceController;
use App\Http\Controllers\Purchase\PurchasePaymentController;
use App\Http\Controllers\Purchase\ReceiveItemController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Inventory\StockTransferController;
use App\Http\Controllers\Master\ContactController;
use App\Http\Controllers\Master\TaxController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Settings\CompanySettingController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Route;

// Auth Routes

// Authenticated Routes
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== SALES ====================
    Route::prefix('sales')->name('sales.')->group(function () {

        // Sales Orders
        Route::resource('orders', SalesOrderController::class)
            ->names('orders');
        Route::post('orders/{salesOrder}/confirm', [SalesOrderController::class, 'confirm'])
            ->name('orders.confirm');
        Route::post('orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])
            ->name('orders.cancel');

        // Sales Invoices
        Route::resource('invoices', SalesInvoiceController::class)
            ->names('invoices');
        Route::post('invoices/{salesInvoice}/post', [SalesInvoiceController::class, 'post'])
            ->name('invoices.post');
        Route::patch('invoices/{salesInvoice}/cancel', [SalesInvoiceController::class, 'cancel'])
            ->name('invoices.cancel');
        Route::get('invoices/{salesInvoice}/print', [SalesInvoiceController::class, 'print'])
            ->name('invoices.print');

        // Sales Receipts
        Route::resource('receipts', SalesReceiptController::class)
            ->names('receipts');
        Route::post('receipts/{salesReceipt}/post', [SalesReceiptController::class, 'post'])
            ->name('receipts.post');
        Route::patch('receipts/{salesReceipt}/cancel', [SalesReceiptController::class, 'cancel'])
            ->name('receipts.cancel');
        Route::get('receipts/{salesReceipt}/print', [SalesReceiptController::class, 'print'])
            ->name('receipts.print');

        // Delivery Orders
        Route::resource('delivery', DeliveryOrderController::class)
            ->names('delivery');
        Route::post('delivery/{deliveryOrder}/post', [DeliveryOrderController::class, 'post'])
            ->name('delivery.post');
        Route::get('delivery/{deliveryOrder}/print', [DeliveryOrderController::class, 'print'])
            ->name('delivery.print');
    });

    // ==================== PURCHASES ====================
    Route::prefix('purchases')->name('purchases.')->group(function () {

        // Purchase Orders
        Route::resource('orders', PurchaseOrderController::class)
            ->names('orders');
        Route::post('orders/{purchaseOrder}/confirm', [PurchaseOrderController::class, 'confirm'])
            ->name('orders.confirm');
        Route::post('orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
            ->name('orders.cancel');
        Route::get('orders/{purchaseOrder}/print', [PurchaseOrderController::class, 'print'])
            ->name('orders.print');

        // Purchase Invoices
        Route::resource('invoices', PurchaseInvoiceController::class)
            ->names('invoices');
        Route::post('invoices/{purchaseInvoice}/post', [PurchaseInvoiceController::class, 'post'])
            ->name('invoices.post');
        Route::patch('invoices/{purchaseInvoice}/cancel', [PurchaseInvoiceController::class, 'cancel'])
            ->name('invoices.cancel');

        // Purchase Payments
        Route::resource('payments', PurchasePaymentController::class)
            ->names('payments');
        Route::post('payments/{purchasePayment}/post', [PurchasePaymentController::class, 'post'])
            ->name('payments.post');

        // Receive Items
        Route::resource('receive', ReceiveItemController::class)
            ->names('receive');
        Route::post('receive/{receiveItem}/post', [ReceiveItemController::class, 'post'])
            ->name('receive.post');
    });

    // ==================== INVENTORY ====================
    Route::prefix('inventory')->name('inventory.')->group(function () {

        // Products
        Route::resource('products', ProductController::class)
            ->names('products');
        Route::get('products/{product}/stock-card', [ProductController::class, 'stockCard'])
            ->name('products.stock-card');

        // Warehouses
        Route::resource('warehouses', WarehouseController::class)
            ->names('warehouses');

        // Stock Adjustments
        Route::resource('adjustments', StockAdjustmentController::class)
            ->names('adjustments');
        Route::post('adjustments/{adjustment}/post', [StockAdjustmentController::class, 'post'])
            ->name('adjustments.post');

        // Stock Transfers
        Route::resource('transfers', StockTransferController::class)
            ->names('transfers');
        Route::post('transfers/{transfer}/post', [StockTransferController::class, 'post'])
            ->name('transfers.post');
    });

    // ==================== FINANCE ====================
    Route::prefix('finance')->name('finance.')->group(function () {

        // Chart of Accounts
        Route::resource('accounts', AccountController::class)
            ->names('accounts');

        // Journals
        Route::resource('journals', JournalController::class)
            ->names('journals');
        Route::post('journals/{journal}/post', [JournalController::class, 'post'])
            ->name('journals.post');
        Route::patch('journals/{journal}/cancel', [JournalController::class, 'cancel'])
            ->name('journals.cancel');
        Route::get('journals/{journal}/print', [JournalController::class, 'print'])
            ->name('journals.print');

        // Cash & Bank
        Route::get('cashbank', [CashBankController::class, 'index'])->name('cashbank.index');
        Route::get('cashbank/transfer', [CashBankController::class, 'transfer'])->name('cashbank.transfer');
        Route::post('cashbank/transfer', [CashBankController::class, 'storeTransfer'])->name('cashbank.transfer.store');

        // Fixed Assets
        Route::resource('assets', FixedAssetController::class)
            ->names('assets');
        Route::post('assets/{fixedAsset}/depreciate', [FixedAssetController::class, 'runDepreciation'])
            ->name('assets.depreciate');
        Route::post('assets/{fixedAsset}/dispose', [FixedAssetController::class, 'dispose'])
            ->name('assets.dispose');
    });

    // ==================== REPORTS ====================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('profit-loss', [ReportController::class, 'profitLoss'])->name('profit-loss');
        Route::get('balance-sheet', [ReportController::class, 'balanceSheet'])->name('balance-sheet');
        Route::get('cash-flow', [ReportController::class, 'cashFlow'])->name('cash-flow');
        Route::get('ledger', [ReportController::class, 'generalLedger'])->name('ledger');
        Route::get('trial-balance', [ReportController::class, 'trialBalance'])->name('trial-balance');
        Route::get('ar', [ReportController::class, 'accountsReceivable'])->name('ar');
        Route::get('ap', [ReportController::class, 'accountsPayable'])->name('ap');
        Route::get('stock', [ReportController::class, 'stockReport'])->name('stock');
        Route::get('sales', [ReportController::class, 'salesReport'])->name('sales');
        Route::get('purchases', [ReportController::class, 'purchaseReport'])->name('purchases');

        // Export
        Route::get('export/{type}', [ReportController::class, 'export'])->name('export');
    });

    // ==================== MASTER DATA ====================
    Route::resource('contacts', ContactController::class);
    Route::resource('taxes', TaxController::class);

    // ==================== SETTINGS ====================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('company', [CompanySettingController::class, 'index'])->name('company');
        Route::put('company', [CompanySettingController::class, 'update'])->name('company.update');
        Route::resource('users', UserController::class)->names('users');
    });

    // Profile
    Route::get('/profile', function () { return view('profile.edit'); })->name('profile.edit');
