<?php

namespace App\Http\Controllers;

use App\Models\Quotation;

class QuotationPrintController extends Controller
{
    public function show(int $id)
    {
        $quotation = Quotation::with(['customer', 'status', 'tax', 'items.unitOfMeasure', 'creator'])
            ->findOrFail($id);

        return view('quotations.print', compact('quotation'));
    }
}
