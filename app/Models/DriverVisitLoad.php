<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverVisitLoad extends Model
{
    protected $guarded = [];
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id');
    }
}
