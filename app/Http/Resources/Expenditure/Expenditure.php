<?php

namespace App\Http\Resources\Expenditure;

use Illuminate\Http\Resources\Json\JsonResource;

class Expenditure extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
