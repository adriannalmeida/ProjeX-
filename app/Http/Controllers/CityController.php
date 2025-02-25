<?php
namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class CityController extends Controller
{
    /**
     * AJAX request for cities.
     */
    public function getCitiesByCountry($countryId)
    {
        try {
            $cities = City::where('country', $countryId)
                ->orderBy('name', 'asc')
                ->get();

            return response()->json($cities);
        } catch (\Exception $e) {
            session()->flash('message', [
                'type' => 'error',
                'text' => 'Unable to fetch cities.',
            ]);
            return response()->json(['error' => 'Unable to fetch cities'], 500);
        }
    }

}