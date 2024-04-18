<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inquiry extends Model
{
    protected $appends = ['isAccepted'];
    /**
     * Get the user that owns the Inquiry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }

    public function getIsAcceptedAttribute()
    {
        if (DriverLoad::where('load_id', $this->load_id)->where('driver_id', $this->driver_id)->count() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
