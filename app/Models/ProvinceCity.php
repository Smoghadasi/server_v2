<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvinceCity extends Model
{
    use HasFactory;
    protected $hidden = ['created_at', 'updated_at'];
    protected $appends = [
        'state',
    ];

    public function getStateAttribute()
    {
        // return $this->parent_id;
        try {
            $state = ProvinceCity::where('id', $this->parent_id)->first();
            if (isset($state->id))
                return $state->name;
            else
                return '';
        } catch (Exception $exception) {
            //throw $th;
        }
    }
}
