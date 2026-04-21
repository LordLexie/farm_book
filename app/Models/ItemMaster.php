<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemMaster extends Model
{
    protected $fillable = ['code', 'name', 'description', 'item_category_id', 'unit_of_measure_id'];

    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function farmItems(): HasMany
    {
        return $this->hasMany(FarmItem::class);
    }
}
