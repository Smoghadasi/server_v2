<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['mobileNumber', 'name', 'lastName', 'nationalCode', 'status'];
    protected $hidden = ['password',  'remember_token'];
    protected $append = ['blockedIp'];

    public function legalPersonality()
    {
        return $this->hasOne(LegalPersonality::class);
    }

    /**
     * @return bool
     */
    public function getBlockedIpAttribute(): bool
    {
        if (BlockedIp::where('user_id', $this->id)->where('userType', ROLE_CUSTOMER)->count())
            return true;

        return false;
    }
}
