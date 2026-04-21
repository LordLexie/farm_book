<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkProduction extends Model
{
    protected $fillable = ['livestock_id', 'farm_session_id', 'date', 'quantity', 'created_by'];

    protected $casts = ['date' => 'date'];

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(FarmLivestock::class, 'livestock_id');
    }

    public function farmSession(): BelongsTo
    {
        return $this->belongsTo(FarmSessionTemplate::class, 'farm_session_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
