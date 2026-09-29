<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Imports\CompaniesImport;
use App\Models\Country;
use App\Models\District;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UtilityController extends Controller
{
    public function countries(Request $request): JsonResponse
    {
        $countries = Country::all(); // Or fetch from JSON file or external API
        return response()->json($countries);
    }


    public function states(Request $request, int $countryId): JsonResponse
    {
        $states = State::where('country_id', $countryId)->get(); // Or fetch from JSON or API based on countryId
        return response()->json($states);
    }

    public function districts(Request $request, int $stateId): JsonResponse
    {
        $districts = District::where('state_id', $stateId)->get(); // Or fetch from JSON or API based on stateId
        return response()->json($districts);
    }

    public function userdataImport()
    {
        $filePath = public_path('uploads/user_data/Category_Wise_31st_Dec_Members_Database_BBC_Final.xlsx');


        // Import the data
        Excel::import(new CompaniesImport(), $filePath);

        //return back()->with('success', 'Data imported successfully!');
        echo "done";

    }
}
