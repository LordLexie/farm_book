<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

class InvoicePrintController extends Controller
{
    public function show(int $id)
    {
        $invoice = Invoice::with(['customer', 'status', 'currency', 'tax', 'items.unitOfMeasure', 'items.invoiceable', 'creator'])
            ->findOrFail($id);

        return view('invoices.print', compact('invoice'));
    }
}
