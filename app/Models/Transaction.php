<?php

namespace App\Models;

use App\Http\Controllers\FleetController;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $appends = ['userTypeTitle', 'payerName', 'payerMobileNumber','FleetDriver', 'paymentDate', 'paymentDates', 'driverFleetName', 'countOfSuccess','countOfAllTries'];

    public function getUserTypeTitleAttribute()
    {
        switch ($this->userType) {
            case ROLE_DRIVER:
                return 'راننده';
            case ROLE_CARGo_OWNER:
                return 'صاحب بار';
            case ROLE_TRANSPORTATION_COMPANY:
                return 'باربری';
        }
    }

    public function getPayerNameAttribute()
    {
        switch ($this->userType) {
            case ROLE_DRIVER:
                $user = Driver::find($this->user_id);
                break;
            case ROLE_CARGo_OWNER:
                $user = Customer::find($this->user_id);
                break;
            case ROLE_TRANSPORTATION_COMPANY:
                $user = Bearing::find($this->user_id);
                break;
        }

        if (isset($user->name))
            return $user->name . ' ' . $user->lastName;

        return 'بدون نام';
    }

    public function getPayerMobileNumberAttribute()
    {
        try {
            switch ($this->userType) {
                case ROLE_DRIVER:
                    $user = Driver::find($this->user_id);
                    return $user->mobileNumber;
                case ROLE_CARGo_OWNER:
                    $user = Customer::find($this->user_id);
                    return $user->mobileNumber;
                case ROLE_TRANSPORTATION_COMPANY:
                    $user = Bearing::find($this->user_id);
                    return $user->mobileNumber;
            }
        } catch (\Exception $e) {
        }
        return 'بدون شماره';
    }

    public function getFleetDriverAttribute()
    {
        try {
            switch ($this->userType) {
                case ROLE_DRIVER:
                    $user = Driver::findOrFail($this->user_id);
                    return $user->driverFleetName;
            }
        } catch (\Exception $e) {
        }
        return 'بدون ناوگان';
    }

    public function getPaymentDateAttribute()
    {
        try {
            $date = explode(' ', $this->created_at);
            return str_replace('-', '/', gregorianDateToPersian($date[0], '-')) . ' ' . $date[1];
        } catch (\Exception $exception) {

        }
        return 'بدون تاریخ';
    }

    public function getPaymentDatesAttribute()
    {
        try {

            $paymentDates = [];
            $transactions = Transaction::where([
                ['user_id', $this->user_id],
                ['status', '>', 0]
            ])
                ->select('id', 'created_at')
                ->orderBy('id', 'desc')
                ->get();

            foreach ($transactions as $transaction)
                $paymentDates[] = gregorianDateToPersian(explode(' ', $transaction->created_at)[0], '-');

            return $paymentDates;

        } catch (\Exception $exception) {
        }

        return [];
    }

    public function getDriverFleetNameAttribute()
    {
        try {
            if ($this->userType == ROLE_DRIVER)
                return Driver::find($this->user_id)->fleetTitle;
        } catch (\Exception $exception) {

        }

        return 'بدون ناوگان';
    }

    public function getCountOfSuccessAttribute()
    {
        try {
            return Transaction::where([
                ['user_id', $this->user_id],
                ['userType', $this->userType],
                ['status', '>', 0]
            ])->count();
        } catch (\Exception $e) {
        }
        return 0;
    }

    public function getCountOfAllTriesAttribute()
    {
        try {
            return Transaction::where([
                ['user_id', $this->user_id],
                ['userType', $this->userType]
            ])->count();
        } catch (\Exception $e) {
        }
        return 0;
    }
}
