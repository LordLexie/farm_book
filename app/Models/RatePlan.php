<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RatePlan extends Model
{
    protected $fillable = ['code', 'name'];

    public function milkRates(): HasMany
    {
        return $this->hasMany(MilkRate::class);
    }
}
