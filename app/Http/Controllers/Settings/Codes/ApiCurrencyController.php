<?php

namespace App\Http\Controllers\Settings\Codes;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Core\Currency;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ApiCurrencyController extends Controller
{
    public function list(): AnonymousResourceCollection
    {
        $currencies = Currency::all();

        return CurrencyResource::collection($currencies);
    }
}
