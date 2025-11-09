<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $service = CustomerService::with('product', 'product.features', 'modules', 'modules.feature', 'options', 'options.product', 'customer', 'customer.user')->where('service_code', $license)->first();

        return $service ? response()->json($service) : response()->json(['valid' => false]);
    }

    public function moduleInfo(Request $request, string $module_slug)
    {
        $license = $request->input('license_key');

        $service = CustomerService::with('modules', 'modules.feature')->where('service_code', $license)->first();

        $module = $service->modules->where('slug', $module_slug)->first();

        return $module ? response()->json($module) : response()->json(['error' => 'module not found'], 404);
    }

    public function moduleActivate(Request $request, string $module_slug)
    {
        $license = $request->input('license_key');

        $service = CustomerService::with('modules')->where('service_code', $license)->first();

        $module = $service->modules->where('slug', $module_slug)->first();

        if (!$module) {
            return response()->json(['error' => 'module not found'], 404);
        }

        $module->is_active = true;
        $module->save();

        return response()->json($module);
    }

    public function moduleDeactivate(Request $request, string $module_slug)
    {
        $license = $request->input('license_key');

        $service = CustomerService::with('modules')->where('service_code', $license)->first();

        $module = $service->modules->where('slug', $module_slug)->first();

        if (!$module) {
            return response()->json(['error' => 'module not found'], 404);
        }

        $module->is_active = false;
        $module->save();

        return response()->json($module);
    }
}
