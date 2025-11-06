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
        return Load::where('operator_id', $this->user_id)
            ->where('created_at', '>=', Carbon::today())
            ->withTrashed()
            ->first();
    }

    public function lastLoad()
    {
        return Load::where('operator_id', $this->user_id)
            ->where('created_at', '>=', Carbon::today())
            ->orderByDesc('created_at')
            ->withTrashed()
            ->first();
    }

    public function numOfLoads()
    {
        return Load::where('operator_id', $this->user_id)
            ->where('created_at', '>=', Carbon::today())
            ->withTrashed()
            ->count();
    }
}
