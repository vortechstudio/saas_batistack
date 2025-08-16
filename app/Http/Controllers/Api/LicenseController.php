<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function validate(Request $request)
    {
        $licenseKey = $request->input('license_key');
        $license = License::where('license_key', $licenseKey)->first();
        return $license ?? false;
    }

    public function info(Request $request)
    {
        $licenseKey = $request->input('license_key');
        $license = License::with('customer', 'product', 'product.includedModules')->where('license_key', $licenseKey)->first();
        return $license;
    }
}
