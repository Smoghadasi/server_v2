<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationUser extends Model
{
    use HasFactory;

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }
}
