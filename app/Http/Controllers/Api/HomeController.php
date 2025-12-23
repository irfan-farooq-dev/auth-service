<?php

namespace App\Http\Controllers\Api;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    // execute this command to generate swagger docs:
    // php artisan l5-swagger:generate

    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Test endpoint",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index()
    {
        return response()->json(['message' => 'Swagger is working']);
    }

    /**
     * @OA\Get(
     *     path="/api/test3",
     *     summary="Fetch endpoint",
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function fetch()
    {
        return response()->json(['message' => 'Swagger is working']);
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function delete($id)
    {
        //
    }
}
