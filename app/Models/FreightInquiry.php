<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreightInquiry extends Model
{
    use HasFactory;

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(ProvinceCity::class, 'from_city_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(ProvinceCity::class, 'to_city_id');
    }

    public function fleet(): BelongsTo
    {
        return $this->belongsTo(Fleet::class);
    }
}
