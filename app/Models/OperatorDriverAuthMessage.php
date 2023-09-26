<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatorDriverAuthMessage extends Model
{
    protected $appends = ['operator'];

    public function getOperatorAttribute()
    {
        try {
            $user = User::find($this->user_id);
            if (isset($user->id))
                return $user->name . ' ' . $user->lastName;
        } catch (\Exception $exception) {

        }

        return '';

    }
}
