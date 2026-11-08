<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryNote extends Model
{
    public const ACTIVE    = 'active';
    public const CANCELLED = 'cancelled';

    protected $fillable = [
        'code', 'invoice_id', 'customer_id', 'date',
        'delivery_address', 'contact_person', 'contact_phone',
        'delivered_by', 'vehicle_reg', 'status', 'notes', 'created_by',
    ];

    protected $casts = ['date' => 'date:Y-m-d'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }
}
