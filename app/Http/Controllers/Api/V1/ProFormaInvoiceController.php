<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProFormaInvoice;
use App\Models\ProFormaInvoiceItem;
use App\Models\Status;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProFormaInvoiceController extends Controller
{
    private function relations(): array
    {
        return ['customer', 'status', 'currency', 'tax', 'items.unitOfMeasure', 'items.invoiceable'];
    }

    public function index(Request $request): JsonResponse
    {
        $perPage   = (int) $request->query('per_page', 15);
        $paginated = ProFormaInvoice::with($this->relations())
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'pro_forma_invoices' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $code   = 'PF-' . str_pad(ProFormaInvoice::count() + 1, 4, '0', STR_PAD_LEFT);
        $active = Status::where('code', 'ACT')->value('id');

        $pf = ProFormaInvoice::create([
            'code'        => $code,
            'customer_id' => $request->input('customer_id'),
            'status_id'   => $request->input('status_id', $active),
            'currency_id' => $request->input('currency_id'),
            'tax_id'      => $request->input('tax_id'),
            'date'        => $request->input('date'),
            'discount'    => $request->input('discount', 0),
            'total'       => 0,
            'created_by'  => $request->user()?->id,
        ]);

        $taxRate = $pf->tax ? (float) $pf->tax->value : 0;
        [$total] = $this->syncItems($pf->id, $request->input('items', []), (float) $pf->discount, $taxRate);

        $pf->update(['total' => $total]);

        return response()->json(['pro_forma_invoice' => $pf->load($this->relations())], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'pro_forma_invoice' => ProFormaInvoice::with($this->relations())->findOrFail($id),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $pf     = ProFormaInvoice::findOrFail($id);
        $fields = array_filter(
            $request->only(['customer_id', 'status_id', 'currency_id', 'tax_id', 'date', 'discount']),
            fn($v) => $v !== null,
        );
        $pf->update($fields);

        if ($request->has('items')) {
            $pf->items()->delete();
            $fresh   = $pf->fresh(['tax']);
            $taxRate = $fresh->tax ? (float) $fresh->tax->value : 0;
            [$total] = $this->syncItems($pf->id, $request->input('items'), (float) $fresh->discount, $taxRate);
            $pf->update(['total' => $total]);
        }

        return response()->json(['pro_forma_invoice' => $pf->load($this->relations())]);
    }

    public function destroy(int $id): JsonResponse
    {
        ProFormaInvoice::findOrFail($id)->delete();
        return response()->json(['message' => 'Pro forma invoice deleted.']);
    }

    public function convert(int $id): JsonResponse
    {
        $pf      = ProFormaInvoice::with(['items', 'tax'])->findOrFail($id);
        $code    = 'INV-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
        $active  = Status::where('code', 'ACT')->value('id');

        $invoice = Invoice::create([
            'code'        => $code,
            'customer_id' => $pf->customer_id,
            'status_id'   => $active,
            'currency_id' => $pf->currency_id,
            'tax_id'      => $pf->tax_id,
            'date'        => $pf->date->format('Y-m-d'),
            'discount'    => $pf->discount,
            'total'       => 0,
            'amount_paid' => 0,
            'balance'     => 0,
            'created_by'  => request()->user()?->id,
        ]);

        $taxRate = $pf->tax ? (float) $pf->tax->value : 0;
        $items   = $pf->items->map(fn($item) => [
            'invoiceable_type'   => $item->invoiceable_type,
            'invoiceable_id'     => $item->invoiceable_id,
            'unit_of_measure_id' => $item->unit_of_measure_id,
            'quantity'           => $item->quantity,
            'unit_price'         => $item->unit_price,
        ])->all();

        $subtotal = 0;
        foreach ($items as $row) {
            $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
            $subtotal += $lineTotal;
            InvoiceItem::create([
                'invoice_id'         => $invoice->id,
                'invoiceable_type'   => $row['invoiceable_type'],
                'invoiceable_id'     => $row['invoiceable_id'],
                'unit_of_measure_id' => $row['unit_of_measure_id'],
                'quantity'           => $row['quantity'],
                'unit_price'         => $row['unit_price'],
                'total'              => $lineTotal,
            ]);
        }

        $afterDiscount = round($subtotal * (1 - (float) $pf->discount / 100), 2);
        $total         = round($afterDiscount * (1 + $taxRate / 100), 2);

        $customer = $invoice->customer;
        ['applied' => $applied, 'balance' => $balance] = CreditService::apply($customer, $total);

        $invoice->update(['total' => $total, 'amount_paid' => $applied, 'balance' => $balance]);

        if ($balance > 0) {
            $customer->increment('amount_due', $balance);
        }

        $invoiceRelations = ['customer', 'status', 'currency', 'tax', 'items.unitOfMeasure', 'items.invoiceable'];
        return response()->json(['invoice' => $invoice->load($invoiceRelations)], 201);
    }

    public function printUrl(int $id): JsonResponse
    {
        return response()->json(['url' => url("/pro-forma-invoices/{$id}/print")]);
    }

    private function syncItems(int $pfId, array $rows, float $discountPct, float $taxRate = 0): array
    {
        $subtotal = 0;
        foreach ($rows as $row) {
            $lineTotal = round($row['quantity'] * $row['unit_price'], 2);
            $subtotal += $lineTotal;
            ProFormaInvoiceItem::create([
                'pro_forma_invoice_id' => $pfId,
                'invoiceable_type'     => $row['invoiceable_type'],
                'invoiceable_id'       => $row['invoiceable_id'],
                'unit_of_measure_id'   => $row['unit_of_measure_id'],
                'quantity'             => $row['quantity'],
                'unit_price'           => $row['unit_price'],
                'total'                => $lineTotal,
            ]);
        }
        $afterDiscount = round($subtotal * (1 - $discountPct / 100), 2);
        $total         = round($afterDiscount * (1 + $taxRate / 100), 2);
        return [$total, $subtotal];
    }
}
