<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer\CustomerService;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function validate(Request $request)
    {
        $license = $request->input('license_key');

        $service = CustomerService::where('service_code', $license)->first();

        return $service ? response()->json(['valid' => true]) : response()->json(['valid' => false]);
    }

    public function info(Request $request)
    {
        $license = $request->input('license_key');

        $service = CustomerService::with('product', 'product.features')->where('service_code', $license)->first();

        return $service ? response()->json($service) : response()->json(['valid' => false]);
    }
}
