<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\FarmController;
use App\Http\Controllers\Api\V1\FarmItemController;
use App\Http\Controllers\Api\V1\FarmLivestockController;
use App\Http\Controllers\Api\V1\FarmConsumptionController;
use App\Http\Controllers\Api\V1\ItemMasterController;
use App\Http\Controllers\Api\V1\MilkProductionController;
use App\Http\Controllers\Api\V1\MilkSaleController;
use App\Http\Controllers\Api\V1\MilkRateController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\ProFormaInvoiceController;
use App\Http\Controllers\Api\V1\BillController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\QuotationController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\LookupController;
use App\Http\Controllers\Api\V1\AnalyticsController;

Route::prefix('v1')->group(function () {

    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Core resources
        Route::apiResource('farms', FarmController::class);
        Route::apiResource('farm-items', FarmItemController::class);
        Route::apiResource('farm-livestocks', FarmLivestockController::class);
        Route::apiResource('farm-consumptions', FarmConsumptionController::class);
        Route::apiResource('item-masters', ItemMasterController::class);
        Route::apiResource('milk-productions', MilkProductionController::class);
        Route::apiResource('milk-sales', MilkSaleController::class);
        Route::apiResource('milk-rates', MilkRateController::class);
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('services', ServiceController::class);
        // Purchases with print URL
        Route::get('purchases/{purchase}/print-url', [PurchaseController::class, 'printUrl']);
        Route::apiResource('purchases', PurchaseController::class);
        // Payments with receipt URL
        Route::get('payments/{id}/print-url', [PaymentController::class, 'printUrl']);
        Route::apiResource('payments', PaymentController::class);
        Route::apiResource('bills', BillController::class);

        // Invoices with print URL
        Route::get('invoices/{invoice}/print-url', [InvoiceController::class, 'printUrl']);
        Route::apiResource('invoices', InvoiceController::class);

        // Pro Forma Invoices with convert + print URL
        Route::post('pro-forma-invoices/{id}/convert', [ProFormaInvoiceController::class, 'convert']);
        Route::get('pro-forma-invoices/{id}/print-url', [ProFormaInvoiceController::class, 'printUrl']);
        Route::apiResource('pro-forma-invoices', ProFormaInvoiceController::class);

        // Quotations with print URL
        Route::get('quotations/{quotation}/print-url', [QuotationController::class, 'printUrl']);
        Route::apiResource('quotations', QuotationController::class);

        // Users / Roles / Permissions
        Route::apiResource('users', UserController::class);
        Route::patch('roles/{role}/permissions', [RoleController::class, 'syncPermissions']);
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index']);
        Route::post('permissions', [PermissionController::class, 'store']);
        Route::patch('permissions/{id}', [PermissionController::class, 'update']);

        // Lookup tables
        Route::get('statuses', [LookupController::class, 'statuses']);
        Route::get('genders', [LookupController::class, 'genders']);
        Route::get('currencies', [LookupController::class, 'currencies']);
        Route::get('billing-cycles', [LookupController::class, 'billingCycles']);
        Route::get('livestock-types', [LookupController::class, 'livestockTypes']);
        Route::get('payment-modes', [LookupController::class, 'paymentModes']);
        Route::get('rate-plans', [LookupController::class, 'ratePlans']);
        Route::get('service-types', [LookupController::class, 'serviceTypes']);
        Route::get('item-categories', [LookupController::class, 'itemCategories']);
        Route::get('farm-sessions', [LookupController::class, 'farmSessions']);
        Route::get('taxes', [LookupController::class, 'taxes']);

        Route::post('{resource}', [LookupController::class, 'store']);
        Route::patch('{resource}/{id}', [LookupController::class, 'update']);

        // Lookup: unit-of-measures
        Route::get('unit-of-measures', [LookupController::class, 'unitOfMeasures']);

        // AnalyticspP
        Route::prefix('analytics')->group(function () {
            Route::get('summary', [AnalyticsController::class, 'summary']);
            Route::get('milk-production-summary', [AnalyticsController::class, 'milkProductionSummary']);
            Route::get('milk-production-trend', [AnalyticsController::class, 'milkProductionTrend']);
            Route::get('milk-production-by-session', [AnalyticsController::class, 'milkProductionBySession']);
            Route::get('milk-production-monthly-trend', [AnalyticsController::class, 'milkProductionMonthlyTrend']);
            Route::get('milk-production-top-animals', [AnalyticsController::class, 'milkProductionTopAnimals']);
            Route::get('milk-sales-trend', [AnalyticsController::class, 'milkSalesTrend']);
            Route::get('recent-milk-sales', [AnalyticsController::class, 'recentMilkSales']);
            Route::get('sales-summary', [AnalyticsController::class, 'salesSummary']);
            Route::get('expense-summary', [AnalyticsController::class, 'expenseSummary']);
            Route::get('expense-breakdown', [AnalyticsController::class, 'expenseBreakdown']);
            Route::get('expense-daily-trend', [AnalyticsController::class, 'expenseDailyTrend']);
            Route::get('expense-monthly-trend', [AnalyticsController::class, 'expenseMonthlyTrend']);
            Route::get('expense-top-categories', [AnalyticsController::class, 'expenseTopCategories']);
            Route::get('expense-recent-purchases', [AnalyticsController::class, 'expenseRecentPurchases']);
            Route::get('recent-purchases', [AnalyticsController::class, 'recentPurchases']);
            Route::get('consumption-summary', [AnalyticsController::class, 'consumptionSummary']);
            Route::get('consumption-by-animal', [AnalyticsController::class, 'consumptionByAnimal']);
            Route::get('consumption-daily-trend', [AnalyticsController::class, 'consumptionDailyTrend']);
            Route::get('consumption-monthly-trend', [AnalyticsController::class, 'consumptionMonthlyTrend']);
            Route::get('consumption-top-items', [AnalyticsController::class, 'consumptionTopItems']);
            Route::get('revenue-trend', [AnalyticsController::class, 'revenueTrend']);
            Route::get('invoice-aging-summary', [AnalyticsController::class, 'invoiceAgingSummary']);
            Route::get('invoice-aging-buckets', [AnalyticsController::class, 'invoiceAgingBuckets']);
            Route::get('invoice-aging-top-customers', [AnalyticsController::class, 'invoiceAgingTopCustomers']);
            Route::get('invoice-aging-trend', [AnalyticsController::class, 'invoiceAgingTrend']);
            Route::get('invoice-aging-recent-overdue', [AnalyticsController::class, 'invoiceAgingRecentOverdue']);
        });
    });
});
