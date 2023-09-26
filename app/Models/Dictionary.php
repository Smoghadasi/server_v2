<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dictionary extends Model
{
    protected $appends = ['originalWord'];

    //
    public function getOriginalWordAttribute()
    {
        try {
            if ($this->type == 'city')
                return City::find($this->original_word_id)->name;
            else if ($this->type == 'fleet')
                return Fleet::find($this->original_word_id)->title;
        } catch (\Exception $exception) {
        }

        return '';
    }
}
