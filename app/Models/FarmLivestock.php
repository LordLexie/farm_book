<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FarmLivestock extends Model
{
    protected $fillable = [
        'code', 'farm_id', 'livestock_type_id', 'name', 'description',
        'date_of_birth', 'breed', 'status_id', 'gender_id',
    ];

    protected $casts = ['date_of_birth' => 'date'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function livestockType(): BelongsTo
    {
        return $this->belongsTo(LivestockType::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    public function milkProductions(): HasMany
    {
        return $this->hasMany(MilkProduction::class, 'livestock_id');
    }
}
