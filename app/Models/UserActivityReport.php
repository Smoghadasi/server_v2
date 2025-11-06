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
            ->where('created_at', '>', date('Y-m-d') . ' 00:00:00')
            ->withTrashed()
            ->first();
    }

    public function lastLoad()
    {
        return Load::where('operator_id', $this->user_id)
            ->where('created_at', '>', date('Y-m-d') . ' 00:00:00')
            ->orderByDesc('created_at')
            ->withTrashed()
            ->first();
    }

    public function numOfLoads()
    {
        return Load::where('operator_id', $this->user_id)
            ->where('created_at', '>', date('Y-m-d') . ' 00:00:00')
            ->withTrashed()
            ->count();
    }
}
