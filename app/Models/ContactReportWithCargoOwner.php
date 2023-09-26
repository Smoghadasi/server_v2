<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactReportWithCargoOwner extends Model
{
    //

    protected $appends = ['firstCal', 'lastCal', 'registerStatus'];

    public function results()
    {
        return $this->hasMany(ContactReportWithCargoOwnerResult::class)->orderBy('id', 'desc');
    }

    public function getLastCalAttribute()
    {
        try {
            $created_at = explode(' ', $this->results[0]->created_at);
            return gregorianDateToPersian($created_at[0], '-') . ' ' . $created_at[1];
        } catch (\Exception $exception) {

        }
        return '';
    }

    public function getFirstCalAttribute()
    {
        try {
            $created_at = explode(' ', $this->results[count($this->results) - 1]->created_at);
            return gregorianDateToPersian($created_at[0], '-') . ' ' . $created_at[1];
        } catch (\Exception $exception) {

        }
        return '';
    }

    public function getRegisterStatusAttribute()
    {
        $userType = '';
        if (Customer::where('mobileNumber', $this->mobileNumber)->count())
            $userType .= ' (صاحب بار) ';
        if (Bearing::where('mobileNumber', $this->mobileNumber)->count())
            $userType .= ' (باربری) ';
        if (Driver::where('mobileNumber', $this->mobileNumber)->count())
            $userType .= ' (راننده) ';

        return $userType;
    }
}
