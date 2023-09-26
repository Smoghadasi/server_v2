<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactReportWithCargoOwnerResult extends Model
{
    protected $appends = ['persianDate'];

    public function operator()
    {
        return $this->hasOne(User::class, 'id', 'operator_id');
    }

    public function getPersianDateAttribute($key)
    {
        try {
            $created_at = explode(' ', $this->created_at);
            return str_replace("-", "/", gregorianDateToPersian($created_at[0], '-')) . ' ' . $created_at[1];
        } catch (\Exception $exception) {

        }
        return 'بدون تاریخ';
    }
}
