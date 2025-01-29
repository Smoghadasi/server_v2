<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Bookmark extends Model
{

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }
}

