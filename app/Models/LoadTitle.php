<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadTitle extends Model
{
    use HasFactory;
    protected $table = 'load_titles'; // جدول تو
    protected $fillable = ['title'];
    public $timestamps = false; // چون این جدول معمولاً زمان ندارد

}
