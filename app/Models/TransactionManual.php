<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionManual extends Model
{
    use HasFactory;

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function getTypeAttribute($type)
    {
        switch ($type) {
            case 'cardToCard':
                $type = 'کارت به کارت';
                break;
            case 'online':
                $type = 'آنلاین';
                break;
        }
        return $type;
    }
}
