<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkSale extends Model
{
    protected $fillable = [
        'code', 'customer_id', 'currency_id', 'date',
        'quantity', 'unit_price', 'total', 'amount_paid', 'balance', 'created_by',
    ];

    protected $casts = ['date' => 'date'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
