<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractCollaboration extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    // public function getIsInsuranceAttribute($name)
    // {
    //     return $name ? 'دارد' : 'ندارد';
    // }

    // public function getContractTypeAttribute($name)
    // {
    //     return $name ? 'دارد' : 'ندارد';
    // }
}
