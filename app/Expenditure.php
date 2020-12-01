<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Expenditure extends Model
{
    protected $table = 'expenditures';

    public function currency(){
        return $this->hasOne(Currency::class, 'currency_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
