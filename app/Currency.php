<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $table = 'currencies';

    public function expenditure(){
        return $this->belongsTo(Expenditure::class);
    }
}
