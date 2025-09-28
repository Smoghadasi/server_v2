<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserActivityReport extends Model
{
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function firstLoad()
    {
        return Load::where('userType', 'operator')
            ->where('user_id', $this->user_id)
            ->where('created_at', '>=', Carbon::today())
            ->first();
    }

    public function lastLoad()
    {
        return Load::where('userType', 'operator')
            ->where('user_id', $this->user_id)
            ->where('created_at', '>=', Carbon::today())
            ->orderByDesc('created_at')
            ->first();
    }

    public function numOfLoads()
    {
        return Load::where('userType', 'operator')
            ->where('user_id', $this->user_id)
            ->where('created_at', '>=', Carbon::today())
            ->count();
    }
}
