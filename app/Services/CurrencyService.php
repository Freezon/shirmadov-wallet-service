<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyService
{
    protected $url = 'https://api.freecurrencyapi.com';

    public function convert($baseCurrency)
    {
        $response = Http::get($this->url.'/v1/latest?apikey='.config('app.currency_key').'&base_currency='.$baseCurrency);
        return $response->json()??null;
    }
}
