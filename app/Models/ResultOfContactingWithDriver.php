<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultOfContactingWithDriver extends Model
{
    //
    protected $appends = ['persianDate'];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function getPersianDateAttribute()
    {
        $created_at = explode(' ', $this->created_at);
        if (isset($created_at[1]))
            return gregorianDateToPersian($created_at[0], '-') . ' ' . $created_at[1];
        return '';
    }

    public function operator()
    {
        return $this->hasOne(User::class, 'id', 'operator_id');
    }
}
