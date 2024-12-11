<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Radio extends Model
{
    use HasFactory;
    protected $appends = ['persianDate'];

    public function getPersianDateAttribute()
    {
        try {
            $date = explode(' ', $this->created_at);
            return str_replace('-', '/', gregorianDateToPersian($date[0], '-'));
        } catch (\Exception $exception) {
        }
        return 'بدون تاریخ';
    }

}
