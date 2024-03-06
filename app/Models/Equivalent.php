<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equivalent extends Model
{
    use HasFactory;

    protected $appends = ['originalWord'];

    public function getOriginalWordAttribute()
    {
        try {
            if ($this->type == 'city')
                return ProvinceCity::find($this->original_word_id)->name;
            else if ($this->type == 'fleet')
                return Fleet::find($this->original_word_id)->title;
        } catch (\Exception $exception) {
        }

        return '';
    }
}
