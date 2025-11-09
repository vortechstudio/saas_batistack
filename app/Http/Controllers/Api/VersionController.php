<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Core\Version;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function index()
    {
        $version = Version::all();
        return response()->json($version);
    }

    public function show($id)
    {
        $version = Version::findOrFail($id);
        return response()->json($version);
    }

    public function create(Request $request)
    {
        $data = $request->all();
        $version = Version::create([
            'version' => $data['release_name']
        ]);

        return response()->json($version);
    }
}
