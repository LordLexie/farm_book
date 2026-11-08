<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryNoteController extends Controller
{
    private function relations(): array
    {
        return ['customer:id,code,name', 'invoice:id,code', 'items.unitOfMeasure'];
    }

    private function respond(DeliveryNote $note, int $status = 200): JsonResponse
    {
        return response()->json(['delivery_note' => $note->load($this->relations())], $status);
    }

    private function reject(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 422);
    }

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'nullable|integer',
            'status'      => 'nullable|in:active,cancelled',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
        ]);

        $perPage   = (int) $request->query('per_page', 15);
        $paginated = DeliveryNote::with($this->relations())
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->query('customer_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('date', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('date', '<=', $request->query('date_to')))
            ->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'delivery_notes' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return $this->respond(DeliveryNote::findOrFail($id));
    }

    /** Editable header fields, shared by create-from-invoice and update. */
    private function headerRules(): array
    {
        return [
            'date'             => 'sometimes|date',
            'delivery_address' => 'nullable|string|max:255',
            'contact_person'   => 'nullable|string|max:255',
            'contact_phone'    => 'nullable|string|max:50',
            'delivered_by'     => 'nullable|string|max:255',
            'vehicle_reg'      => 'nullable|string|max:30',
            'notes'            => 'nullable|string',
        ];
    }

    /** Header values taken from the invoice's customer. */
    private function defaultsFor(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'date'             => now()->toDateString(),
            'delivery_address' => $customer?->address,
            'contact_person'   => $customer?->contact_person ?: $customer?->name,
            'contact_phone'    => $customer?->phone,
        ];
    }

    private function activeNoteFor(Invoice $invoice): ?DeliveryNote
    {
        return $invoice->deliveryNotes()->where('status', '!=', DeliveryNote::CANCELLED)->first();
    }

    /** Prefill for the "new delivery note" form, plus any active note the invoice already has. */
    public function draft(int $invoiceId): JsonResponse
    {
        $invoice  = Invoice::with('customer')->findOrFail($invoiceId);
        $existing = $this->activeNoteFor($invoice);

        return response()->json([
            'draft'    => $this->defaultsFor($invoice),
            'existing' => $existing ? ['id' => $existing->id, 'code' => $existing->code] : null,
            'invoice'  => ['id' => $invoice->id, 'code' => $invoice->code, 'customer' => $invoice->customer?->name],
        ]);
    }

    /** Generate a delivery note from an invoice's items. Header fields sent in the body override the customer defaults. */
    public function fromInvoice(Request $request, int $invoiceId): JsonResponse
    {
        $data    = $request->validate($this->headerRules());
        $invoice = Invoice::with(['customer', 'items.invoiceable'])->findOrFail($invoiceId);

        $existing = $this->activeNoteFor($invoice);
        if ($existing) {
            return response()->json([
                'message'       => "Invoice {$invoice->code} already has delivery note {$existing->code}.",
                'delivery_note' => ['id' => $existing->id, 'code' => $existing->code],
            ], 409);
        }

        $note = DB::transaction(function () use ($invoice, $request, $data) {
            $note = DeliveryNote::create(array_merge($this->defaultsFor($invoice), $data, [
                'code'        => 'DN-' . str_pad(DeliveryNote::count() + 1, 4, '0', STR_PAD_LEFT),
                'invoice_id'  => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'status'      => DeliveryNote::ACTIVE,
                'created_by'  => $request->user()?->id,
            ]));

            foreach ($invoice->items as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id'   => $note->id,
                    'invoice_item_id'    => $item->id,
                    'name'               => $item->name ?? $item->invoiceable?->name ?? 'Item',
                    'description'        => $item->description,
                    'unit_of_measure_id' => $item->unit_of_measure_id,
                    'qty_ordered'        => $item->quantity,
                ]);
            }

            return $note;
        });

        return $this->respond($note, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $note = DeliveryNote::findOrFail($id);
        if ($note->status === DeliveryNote::CANCELLED) {
            return $this->reject('A cancelled delivery note can no longer be edited.');
        }

        $data = $request->validate($this->headerRules());

        $note->update($data);

        return $this->respond($note);
    }

    public function cancel(int $id): JsonResponse
    {
        $note = DeliveryNote::findOrFail($id);
        if ($note->status === DeliveryNote::CANCELLED) {
            return $this->reject('This delivery note is already cancelled.');
        }

        $note->update(['status' => DeliveryNote::CANCELLED]);

        return $this->respond($note);
    }

    public function destroy(int $id): JsonResponse
    {
        $note = DeliveryNote::findOrFail($id);
        if ($note->status !== DeliveryNote::ACTIVE) {
            return $this->reject('Only active delivery notes can be deleted.');
        }

        $note->delete();

        return response()->json(null, 204);
    }
}
