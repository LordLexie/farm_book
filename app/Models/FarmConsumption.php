<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmConsumption extends Model
{
    protected $fillable = [
        'livestock_id', 'farm_session_id', 'farm_item_id',
        'quantity', 'consumption_date', 'created_by',
    ];

    protected $casts = ['consumption_date' => 'date'];

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(FarmLivestock::class, 'livestock_id');
    }

    public function farmSession(): BelongsTo
    {
        return $this->belongsTo(FarmSession::class);
    }

    public function farmItem(): BelongsTo
    {
        return $this->belongsTo(FarmItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
