<?php

namespace App\Http\Controllers;

use App\Models\ProFormaInvoice;

class ProFormaInvoicePrintController extends Controller
{
    public function show(int $id)
    {
        $proFormaInvoice = ProFormaInvoice::with([
            'customer', 'status', 'currency', 'tax',
            'items.unitOfMeasure', 'items.invoiceable', 'creator',
        ])->findOrFail($id);

        return view('pro-forma-invoices.print', compact('proFormaInvoice'));
    }
}
