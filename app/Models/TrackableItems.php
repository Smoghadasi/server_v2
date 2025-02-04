<?php

namespace App\Models;

use App\Traits\SelfParental;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackableItems extends Model
{
    use HasFactory, SelfParental;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
