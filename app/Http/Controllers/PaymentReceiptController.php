<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class PaymentReceiptController extends Controller
{
    public function show(int $id)
    {
        $payment = Payment::with(['customer', 'currency', 'paymentMode', 'creator'])
            ->findOrFail($id);
        return view('payments.receipt', compact('payment'));
    }
}
