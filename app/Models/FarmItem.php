<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmItem extends Model
{
    protected $fillable = ['code', 'farm_id', 'item_master_id', 'quantity', 'status_id'];

    protected $appends = ['name'];

    public function getNameAttribute(): string
    {
        return $this->itemMaster?->name ?? $this->code;
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(ItemMaster::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
