<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserActivityReport extends Model
{
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
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
        $persian_date = gregorianDateToPersian(date('Y/m/d', time()), '/');
        try {
            $numOfLoads = UserActivityReport::where('user_id', $this->user_id)
                ->where('persian_date', $persian_date)
                ->first();
            return $numOfLoads->count;
        } catch (\Throwable $th) {
            return 0;
        }
    }
    public function numOfDeletedLoads()
    {
        return Load::where('operator_id', $this->user_id)
            ->where('created_at', '>', date('Y-m-d') . ' 00:00:00')
            ->onlyTrashed()
            ->count();
    }
    public function numOfLoadsReject()
    {
        return CargoConvertList::where('operator_id', $this->user_id)
            // ->where('created_at', '>', date('Y-m-d') . ' 00:00:00')
            ->where('rejected', '=', 1)
            ->count();
    }
}
