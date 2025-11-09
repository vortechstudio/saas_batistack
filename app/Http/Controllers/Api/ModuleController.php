<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product\Product;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function list(Request $request)
    {
        $products = Product::where('category', 'module')->get();

        return $products ? response()->json($products) : response()->json(['error' => 'modules not found'], 404);
    }

    public function info(Request $request, string $module_slug)
    {
        $product = Product::where('category', 'module')->where('slug', $module_slug)->first();

        return $product ? response()->json($product) : response()->json(['error' => 'module not found'], 404);
    }
}
