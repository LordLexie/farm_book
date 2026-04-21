<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProFormaInvoiceItem extends Model
{
    protected $fillable = [
        'pro_forma_invoice_id', 'invoiceable_type', 'invoiceable_id',
        'unit_of_measure_id', 'quantity', 'unit_price', 'total',
    ];

    public function proFormaInvoice(): BelongsTo
    {
        return $this->belongsTo(ProFormaInvoice::class);
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }
}
