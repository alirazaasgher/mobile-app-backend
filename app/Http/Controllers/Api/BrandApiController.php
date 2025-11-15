<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Brand;

class BrandApiController extends Controller
{

    public function index(){
        return response()->json(Brand::all());
    }

}
