<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NovaPoshtaController extends Controller
{
    public function getCities()
    {
        $apiKey = config('services.novaposhta.key');

        $response = Http::withOptions(['verify' => false]) // ⬅️ ОТУТ
            ->post('https://api.novaposhta.ua/v2.0/json/', [
                'apiKey' => $apiKey,
                'modelName' => 'Address',
                'calledMethod' => 'getCities',
            ]);

        return $response->json();
    }

    public function getWarehouses(Request $request)
    {
        $request->validate([
            'cityRef' => 'required|string',
        ]);

        $apiKey = config('services.novaposhta.key');

        $response = Http::withOptions(['verify' => false]) // ⬅️ І ТУТ
            ->post('https://api.novaposhta.ua/v2.0/json/', [
                'apiKey' => $apiKey,
                'modelName' => 'AddressGeneral',
                'calledMethod' => 'getWarehouses',
                'methodProperties' => [
                    'CityRef' => $request->cityRef,
                ],
            ]);

        return $response->json();
    }
}
