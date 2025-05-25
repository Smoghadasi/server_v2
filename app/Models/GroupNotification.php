<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupNotification extends Model
{
    use HasFactory;

    public function manualNotificationRecipients(): HasMany
    {
        return $this->hasMany(ManualNotificationRecipient::class, 'group_id');
    }
}
