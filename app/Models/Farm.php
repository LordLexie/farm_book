<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    protected $fillable = ['code', 'name', 'longitude', 'latitude', 'status_id'];

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function livestocks(): HasMany
    {
        return $this->hasMany(FarmLivestock::class);
    }

    public function farmItems(): HasMany
    {
        return $this->hasMany(FarmItem::class);
    }

    public function farmSessions(): HasMany
    {
        return $this->hasMany(FarmSession::class);
    }
}
