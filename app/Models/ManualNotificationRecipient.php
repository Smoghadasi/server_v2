<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ManualNotificationRecipient extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }

    public function groupNotification(): BelongsTo
    {
        return $this->belongsTo(GroupNotification::class, 'group_id');
    }
}
