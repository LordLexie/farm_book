<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmSession extends Model
{
    protected $fillable = ['code', 'name', 'farm_id', 'session_type_id', 'started_at', 'notes'];

    protected $casts = ['started_at' => 'datetime'];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function sessionType(): BelongsTo
    {
        return $this->belongsTo(FarmSessionType::class, 'session_type_id');
    }
}
