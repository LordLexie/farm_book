<?php

use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\QuotationPrintController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['status' => 'ok']));

Route::get('/quotations/{id}/print', [QuotationPrintController::class, 'show']);
Route::get('/invoices/{id}/print', [InvoicePrintController::class, 'show']);
Route::get('/payments/{id}/receipt', [PaymentReceiptController::class, 'show']);
