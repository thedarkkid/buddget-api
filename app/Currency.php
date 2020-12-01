<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class Currency extends Model
{
    protected $table = 'currencies';

    /**
     * _get
     * returns a collection of currencies based on the specified request parameters;
     * @param  mixed $request
     * @return mixed
     */
    public static function _get(Request $request){
        return self::when($request->name, function($query) use ($request) {
            return $query->where('name',  'LIKE', "%{$request->name}%");
        })->when($request->acronym, function($query) use ($request) {
            return $query->where('acronym',  'LIKE', "%{$request->acronym}%");
        });
    }

    /**
     * expenditure
     *  returns expenditure eloquent relationships
     * @return BelongsTo|void
     */
    public function expenditure(){
        return $this->belongsTo(Expenditure::class);
    }
}
