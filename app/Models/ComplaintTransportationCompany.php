<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintTransportationCompany extends Model
{
    //

    protected $appends = ['transportationCompany'];

    public function getTransportationCompanyAttribute()
    {
        try {
            $transportationCompany = Bearing::find($this->transportationCompany_id);
            return $transportationCompany->title;
        } catch (\Exception $exception) {

        }

        return '';
    }
}
