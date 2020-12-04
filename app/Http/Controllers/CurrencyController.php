<?php

namespace App\Http\Controllers;

use App\Http\Requests\Currency\CreateCurrencyRequest;
use App\Http\Requests\Currency\UpdateCurrencyRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Currency;
use App\Http\Resources\Currency\Currency as CurrencyResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection|Response
     */
    public function index(Request $request)
    {
        $limit = $request->has('_limit') ? $request->_limit : 20;      // check if a limit was specified else return default limit.
        $currencies = Currency::_get($request)                              // Gets all currencies based on the request parameters
            ->orderBy('id', 'asc')
            ->paginate($limit);
        return CurrencyResource::collection($currencies);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateCurrencyRequest $request
     * @return CurrencyResource|Response
     */
    public function store(CreateCurrencyRequest $request)
    {
        $validated = $request->validated();     //return validated data and throw an error if there is one.
        $currency = new Currency($validated);   // create new currency object from validated data.
        $currency->save();                      // save currency object in db

        return new CurrencyResource($currency);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateCurrencyRequest $request
     * @param int $id
     * @return CurrencyResource|Response
     */
    public function update(UpdateCurrencyRequest $request, $id)
    {
        try{
            $currency = Currency::findOrFail($id);
            $currency->update($request->validated());
            if($currency->save()) return new CurrencyResource($currency);
        }catch (ModelNotFoundException $e){
            $errors = ["Currency with ID $id not found"];
            return response(['errors'=> $errors], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
