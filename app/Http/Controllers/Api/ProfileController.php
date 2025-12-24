<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profile",
     *     tags={"Profile"},
     *     summary="Get current user profile",
     *     description="Returns the authenticated user's profile. Requires Authorization header with Bearer token.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-24T12:34:56Z")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show(Request $request)
    {
        // The JwtMiddleware attached decoded payload to $request->auth
        $userId = $request->auth->sub;

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'created_at' => $user->created_at,
        ]);
    }
}
