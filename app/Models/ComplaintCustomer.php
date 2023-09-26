<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintCustomer extends Model
{
    protected $appends = ['customer'];

    public function getCustomerAttribute($key)
    {
        try {

            $customer = Customer::find($this->customer_id);
            return $customer->name . '' . $customer->lastName;

        } catch (\Exception $exception) {
        }

        return '';
    }
}
