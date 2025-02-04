<?php

namespace App\Models;

use App\Traits\SelfParental;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackableItems extends Model
{
    use HasFactory, SelfParental;
}
