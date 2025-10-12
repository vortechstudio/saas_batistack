<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer\CustomerService;
use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function list(Request $request)
    {
        $service = CustomerService::where('service_code', $request->input('license_key'))->first();
        if (!$service) {
            return response()->json(['error' => 'License not found'], 404);
        }
        // Récupération des sauvegardes
        $backups = $service->backups()->get();

        return response()->json(['backups' => $backups]);
    }

    public function store(Request $request)
    {
        $service = CustomerService::where('service_code', $request->input('license_key'))->first();
        if (!$service) {
            return response()->json(['error' => 'License not found'], 404);
        }
        // Insertion de la sauvegarde
        $service->backups()->create([
            'customer_service_id' => $service->id
        ]);

        return response()->json();
    }
}
