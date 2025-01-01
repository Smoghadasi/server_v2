<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionManual extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $appends = ['lastPaymentDate', 'firstPaymentDate', 'lastActiveDate'];

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

    public function getLastPaymentDateAttribute()
    {
        try {
            $transaction = TransactionManual::where([
                ['driver_id', $this->driver_id]
            ])
                ->select('id', 'created_at', 'date')
                ->orderBy('created_at', 'asc')
                ->first();
            // $date = explode(' ', $transaction->created_at);
            return $transaction->date;
        } catch (\Exception $exception) {
        }
        return 'بدون تاریخ';
    }

    public function getFirstPaymentDateAttribute()
    {
        try {
            $transaction = TransactionManual::where('driver_id', $this->driver_id)
                ->select('id', 'created_at', 'date')
                ->orderByDesc('created_at')
                ->first();
            $date = explode(' ', $transaction->created_at);

            return gregorianDateToPersian($transaction->created_at, '-', true) . ' | ' .  $date[1];
        } catch (\Exception $exception) {
        }
        return 'بدون تاریخ';
    }
    public function getLastActiveDateAttribute()
    {
        try {
            // $driver = Driver::find($this->driver_id);
            $transaction = Transaction::where('userType', 'driver')
                ->where('user_id', $this->driver_id)
                ->whereIn('status', [100, 101])
                ->latest('created_at')
                ->first();

            return gregorianDateToPersian($transaction->created_at, '-', true) ?? null;
        } catch (\Exception $exception) {
        }
        return 'بدون تاریخ';
    }
}
